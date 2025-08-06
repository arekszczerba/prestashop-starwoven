<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace PrestaShop\PrestaShop\Adapter\Carrier\QueryHandler;

use Address;
use PrestaShop\PrestaShop\Adapter\Address\Repository\AddressRepository;
use PrestaShop\PrestaShop\Adapter\Carrier\Repository\CarrierRepository;
use PrestaShop\PrestaShop\Adapter\Country\Repository\CountryRepository;
use PrestaShop\PrestaShop\Adapter\Product\Repository\ProductRepository;
use PrestaShop\PrestaShop\Adapter\Zone\Repository\ZoneRepository;
use PrestaShop\PrestaShop\Core\CommandBus\Attributes\AsQueryHandler;
use PrestaShop\PrestaShop\Core\Context\LanguageContext;
use PrestaShop\PrestaShop\Core\Context\ShopContext;
use PrestaShop\PrestaShop\Core\Domain\Address\ValueObject\AddressId;
use PrestaShop\PrestaShop\Core\Domain\Carrier\Query\GetAvailableCarriers;
use PrestaShop\PrestaShop\Core\Domain\Carrier\QueryHandler\GetAvailableCarriersHandlerInterface;
use PrestaShop\PrestaShop\Core\Domain\Carrier\QueryResult\CarrierSummary;
use PrestaShop\PrestaShop\Core\Domain\Carrier\QueryResult\FilteredCarrier;
use PrestaShop\PrestaShop\Core\Domain\Carrier\QueryResult\GetCarriersResult;
use PrestaShop\PrestaShop\Core\Domain\Carrier\QueryResult\ProductSummary;
use PrestaShop\PrestaShop\Core\Domain\Carrier\ValueObject\CarrierId;
use PrestaShop\PrestaShop\Core\Domain\Country\ValueObject\CountryId;
use PrestaShop\PrestaShop\Core\Domain\Product\ValueObject\ProductQuantity;
use PrestaShop\PrestaShop\Core\Domain\Shop\ValueObject\ShopId;
use PrestaShop\PrestaShop\Core\Domain\Zone\ValueObject\ZoneId;
use Product;
use RuntimeException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsQueryHandler]
class GetAvailableCarriersHandler implements GetAvailableCarriersHandlerInterface
{
    public function __construct(
        private readonly CarrierRepository $carrierRepository,
        private readonly ProductRepository $productRepository,
        private readonly AddressRepository $addressRepository,
        private readonly CountryRepository $countryRepository,
        private readonly ZoneRepository $zoneRepository,
        private readonly LanguageContext $languageContext,
        #[Autowire(service: 'prestashop.default.language.context')]
        private readonly LanguageContext $defaultLanguageContext,
        private readonly ShopContext $shopContext
    ) {
    }

    /**
     * Handle the query to retrieve available and filtered carriers based on products, delivery address and constraints.
     *
     * @param GetAvailableCarriers $query
     *
     * @return GetCarriersResult
     */
    public function handle(GetAvailableCarriers $query): GetCarriersResult
    {
        $shopId = new ShopId($this->shopContext->getId());
        $productQuantities = $query->getProductQuantities();
        $productIds = $query->getProductIds();

        // Load all products
        $products = $this->loadProducts($productQuantities);

        // Validate and resolve address
        $address = $this->getAddress($query->getAddressId());
        $countryId = new CountryId($address->id_country);
        $this->assertCountryIsActive($countryId);
        $zoneId = $this->getZoneFromAddress($countryId);

        // Load carriers mapped per product
        $carriersMapping = $this->carrierRepository->findCarriersByProductIds($productIds, $shopId);

        // Compute common carriers shared by all products
        $commonCarriers = $this->getCommonCarriers($carriersMapping);
        $carriersIndex = $this->indexCarriers($carriersMapping);

        $eligibleCarrierIds = [];
        foreach ($commonCarriers as $carrierId) {
            $carrierData = $carriersIndex[$carrierId];

            if ($this->isCarrierEligible($carrierData, $zoneId, $products, $productQuantities)) {
                $eligibleCarrierIds[] = $carrierId;
            }
        }

        $availableCarriers = [];
        foreach ($eligibleCarrierIds as $carrierId) {
            $carrier = $carriersIndex[$carrierId];
            $availableCarriers[] = new CarrierSummary($carrier['id_carrier'], $carrier['name']);
        }

        // Compute filtered carriers (carriers not available for all products)
        $removedCarriers = [];
        $allCarrierIds = $this->mapCarrierToProducts($carriersMapping);
        foreach ($allCarrierIds as $carrierId => $productIds) {
            if (!in_array($carrierId, $eligibleCarrierIds, true)) {
                $carrier = $carriersIndex[$carrierId];
                $productPreviews = array_map(function (int $pid) use ($products) {
                    $product = $products[$pid];

                    return new ProductSummary($product->id, $this->getProductName($product));
                }, array_keys($productIds));

                $removedCarriers[] = new FilteredCarrier(
                    $productPreviews,
                    new CarrierSummary($carrier['id_carrier'], $carrier['name'])
                );
            }
        }

        return new GetCarriersResult($availableCarriers, $removedCarriers);
    }

    /**
     * @param ProductQuantity[] $productQuantities
     *
     * @return Product[]
     */
    private function loadProducts(array $productQuantities): array
    {
        $products = [];
        foreach ($productQuantities as $pq) {
            $productId = $pq->getProductId();
            $products[$productId->getValue()] = $this->productRepository->get(
                $productId,
                new ShopId($this->shopContext->getId())
            );
        }

        return $products;
    }

    /**
     * Ensures country is active.
     */
    private function assertCountryIsActive(CountryId $countryId): void
    {
        $country = $this->countryRepository->get($countryId);
        if (!$country->active) {
            throw new RuntimeException('Country is not active for address.');
        }
    }

    /**
     * Retrieves address from address ID.
     */
    private function getAddress(?AddressId $addressId): Address
    {
        if (!$addressId) {
            throw new RuntimeException('No delivery address provided.');
        }

        return $this->addressRepository->get($addressId);
    }

    /**
     * Retrieves shipping zone from country ID.
     */
    private function getZoneFromAddress(CountryId $countryId): ZoneId
    {
        return $this->zoneRepository->getZoneIdByCountryId($countryId);
    }

    /**
     * Compute intersection of carriers for all products.
     *
     * @param array<int, array<int, array{id_carrier: int, name: string}>> $carriersMapping
     *
     * @return int[] List of carrier IDs
     */
    private function getCommonCarriers(array $carriersMapping): array
    {
        $common = null;

        foreach ($carriersMapping as $carriers) {
            $ids = array_column($carriers, 'id_carrier');
            $common = is_null($common) ? $ids : array_intersect($common, $ids);
        }

        return $common ?? [];
    }

    /**
     * Index carriers by ID for quick lookup.
     *
     * @param array<int, array<int, array{id_carrier: int, name: string}>> $carriersMapping
     *
     * @return array<int, array{id_carrier: int, name: string}>
     */
    private function indexCarriers(array $carriersMapping): array
    {
        $index = [];

        foreach ($carriersMapping as $carriers) {
            foreach ($carriers as $carrier) {
                $index[$carrier['id_carrier']] = $carrier;
            }
        }

        return $index;
    }

    /**
     * Map carriers to products they apply to.
     *
     * @param array<int, array<int, array{id_carrier: int, name: string}>> $carriersMapping
     *
     * @return array<int, array<int, true>> [carrierId => [productId => true]]
     */
    private function mapCarrierToProducts(array $carriersMapping): array
    {
        $map = [];
        foreach ($carriersMapping as $productId => $carriers) {
            foreach ($carriers as $carrier) {
                $map[$carrier['id_carrier']][$productId] = true;
            }
        }

        return $map;
    }

    /**
     * Checks if a carrier is eligible for the current zone and constraints.
     *
     * @param array{id_carrier: int, name: string} $carrier
     * @param ZoneId $zoneId
     * @param Product[] $products
     * @param ProductQuantity[] $quantities
     *
     * @return bool
     */
    private function isCarrierEligible(array $carrier, ZoneId $zoneId, array $products, array $quantities): bool
    {
        // Check if carrier supports the zone
        if (!$this->carrierRepository->checkCarrierZone(new CarrierId($carrier['id_carrier']), $zoneId)) {
            return false;
        }

        // Compute total weight and max dimensions
        $totalWeight = 0;
        $maxW = $maxH = $maxD = 0;

        foreach ($quantities as $pq) {
            $product = $products[$pq->getProductId()->getValue()];
            $qty = $pq->getQuantity();

            $totalWeight += $product->weight * $qty;
            $maxW = max($maxW, $product->width);
            $maxH = max($maxH, $product->height);
            $maxD = max($maxD, $product->depth);
        }

        $limits = $this->carrierRepository->getCarrierConstraints(new CarrierId($carrier['id_carrier']));

        return $totalWeight <= $limits->maxWeight
            && $maxW <= $limits->maxWidth
            && $maxH <= $limits->maxHeight
            && $maxD <= $limits->maxDepth;
    }

    /**
     * Get translated product name from current or fallback language.
     */
    private function getProductName(Product $product): string
    {
        $name = $product->name;
        if (!is_array($name)) {
            return $name;
        }

        $langId = $this->languageContext->getId();
        $fallbackId = $this->defaultLanguageContext->getId();

        if (isset($name[$langId])) {
            return $name[$langId];
        }

        if (isset($name[$fallbackId])) {
            return $name[$fallbackId];
        }

        throw new RuntimeException(sprintf(
            'Product name not found for product ID %d in current (%d) or default (%d) language.',
            $product->id,
            $langId,
            $fallbackId
        ));
    }
}
