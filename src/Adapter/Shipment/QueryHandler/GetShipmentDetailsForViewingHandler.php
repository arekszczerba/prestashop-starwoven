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

namespace PrestaShop\PrestaShop\Adapter\Shipment\QueryHandler;

use PrestaShop\Decimal\DecimalNumber;
use PrestaShop\PrestaShop\Core\CommandBus\Attributes\AsQueryHandler;
use PrestaShop\PrestaShop\Core\Domain\Shipment\Exception\ShipmentNotFoundException;
use PrestaShop\PrestaShop\Core\Domain\Shipment\Query\GetShipmentDetails;
use PrestaShop\PrestaShop\Core\Domain\Shipment\QueryHandler\GetShipmentDetailsForViewingHandlerInterface;
use PrestaShop\PrestaShop\Core\Domain\Shipment\QueryResult\OrderShipment;
use PrestaShop\PrestaShop\Core\Domain\Shipment\QueryResult\OrderShipmentProduct;
use PrestaShopBundle\Entity\Repository\ShipmentRepository;
use PrestaShopBundle\Entity\ShipmentProduct;
use Throwable;

#[AsQueryHandler]
class GetShipmentDetailsForViewingHandler implements GetShipmentDetailsForViewingHandlerInterface
{
    public function __construct(
        private readonly ShipmentRepository $repository,
    ) {
    }

    /**
     * @param GetShipmentDetails $query
     *
     * @return OrderShipment
     */
    public function handle(GetShipmentDetails $query): OrderShipment
    {
        $shipment = [];
        $shipmentId = $query->getShipmentId()->getValue();

        try {
            $result = $this->repository->findOneBy(['id' => $shipmentId]);
        } catch (Throwable $e) {
            throw new ShipmentNotFoundException(sprintf('Could not find shipment with id "%s"', $shipmentId), 0, $e);
        }
        if (!empty($result)) {
            $shipment = new OrderShipment(
                $result->getId(),
                $result->getOrderId(),
                $result->getCarrierId(),
                $result->getAddressId(),
                new DecimalNumber((string) $result->getShippingCostTaxExcluded()),
                new DecimalNumber((string) $result->getShippingCostTaxIncluded()),
                array_map([$this, 'convert'], $result->getProducts()->toArray()),
                $result->getTrakingNumber(),
                $result->getShippedAt(),
                $result->getDeliveredAt(),
                $result->getCancelledAt(),
            );
        }

        return $shipment;
    }

    private function convert(ShipmentProduct $product)
    {
        return new OrderShipmentProduct(
            $product->getOrderDetailId(),
            $product->getQuantity(),
        );
    }
}
