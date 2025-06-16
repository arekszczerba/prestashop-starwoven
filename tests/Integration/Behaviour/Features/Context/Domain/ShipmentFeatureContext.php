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

declare(strict_types=1);

namespace Tests\Integration\Behaviour\Features\Context\Domain;

use Behat\Gherkin\Node\TableNode;
use Exception;
use OrderDetail;
use Order;
use PHPUnit\Framework\Assert;
use PrestaShop\PrestaShop\Core\Domain\Shipment\Command\MergeProductsToShipment;
use PrestaShop\PrestaShop\Core\Domain\Shipment\Command\SwitchShipmentCarrierCommand;
use PrestaShop\PrestaShop\Core\Domain\Shipment\Query\GetOrderShipments;
use PrestaShop\PrestaShop\Core\Domain\Shipment\Query\GetShipmentProducts;
use PrestaShop\PrestaShop\Core\Domain\Shipment\Query\ListAvailableShipments;
use RuntimeException;
use Tests\Integration\Behaviour\Features\Context\SharedStorage;

class ShipmentFeatureContext extends AbstractDomainFeatureContext
{
    /**
     * @When I switch the carrier for shipment :shipmentReference to :carrierReference
     */
    public function switchShipmentCarrier(string $shipmentReference, string $carrierReference): void
    {
        $shipmentId = SharedStorage::getStorage()->get($shipmentReference);
        $carrierId = $this->referenceToId($carrierReference);

        try {
            $this->getCommandBus()->handle(new SwitchShipmentCarrierCommand($shipmentId, $carrierId));
        } catch (Exception $error) {
            throw new RuntimeException(sprintf('Error while switching shipment "%s" to carrier "%s" : %s', $shipmentReference, $carrierReference, $error->getMessage()));
        }
    }

    /**
     * @Then the order :orderReference should have the following shipments:
     *
     * @param string $orderReference
     * @param TableNode $table
     *
     * @throws RuntimeException
     */
    public function verifyOrderShipment(string $orderReference, TableNode $table)
    {
        $data = $table->getColumnsHash();
        $orderId = $this->referenceToId($orderReference);
        $shipments = $this->getQueryBus()->handle(
            new GetOrderShipments($orderId)
        );

        if (count($shipments) === 0) {
            $msg = 'Order [' . $orderId . '] has no shipments';
            throw new RuntimeException($msg);
        }

        for ($i = 0; $i < count($data); ++$i) {
            $shipmentData = $data[$i];
            $shipment = $shipments[$i];
            $carrierReference = $data[$i]['carrier'];
            $carrierId = $this->referenceToId($carrierReference);
            $addressId = $this->referenceToId($data[$i]['address']);

            if ($shipment->getOrderId() !== $orderId) {
                throw new RuntimeException('Shipment [' . $shipment->getId() . '] does not belong to order [' . $orderId . ']');
            }

            Assert::assertEquals($shipment->getTrackingNumber(), $shipmentData['tracking_number'], 'Wrong tracking number for ' . $carrierReference);
            Assert::assertEquals($shipment->getCarrierId(), $carrierId, 'Wrong carrier ID for ' . $carrierReference);
            Assert::assertEquals($shipment->getAddressId(), $addressId, 'Wrong address ID for ' . $carrierReference);
            Assert::assertEquals($shipment->getShippingCostTaxExcluded(), $shipmentData['shipping_cost_tax_excl'], 'Wrong shipping cast tax excluded for ' . $carrierReference);
            Assert::assertEquals($shipment->getShippingCostTaxIncluded(), $shipmentData['shipping_cost_tax_incl'], 'Wrong shipping cast tax included for ' . $carrierReference);
            SharedStorage::getStorage()->set($shipmentData['shipment'], $shipment->getId());
        }
    }

    /**
     * @Then the shipment :shipmentReference should have the following products:
     *
     * @param string $shipmentReference
     * @param TableNode $table
     */
    public function verifyShipmentProducts(string $shipmentReference, TableNode $table)
    {
        $data = $table->getColumnsHash();
        $shipmentId = SharedStorage::getStorage()->get($shipmentReference);

        $shipmentProducts = $this->getQueryBus()->handle(
            new GetShipmentProducts($shipmentId)
        );

        for ($i = 0; $i < count($shipmentProducts); ++$i) {
            Assert::assertEquals($shipmentProducts[$i]->getQuantity(), (int) $data[$i]['quantity']);
            Assert::assertEquals($shipmentProducts[$i]->getProductName(), $data[$i]['product_name']);
        }
    }

    /**
     * @Then the shipment :shipmentReference should be deleted
     *
     * @param string $shipmentReference
     */
    public function verifyIfShipmentIsDeleted(string $shipmentReference)
    {
        $shipmentId = SharedStorage::getStorage()->get($shipmentReference);

        $shipmentProducts = $this->getQueryBus()->handle(
            new GetShipmentProducts($shipmentId)
        );

        Assert::assertEmpty($shipmentProducts);
    }

    /**
     * @Given I merge product from :sourceShipment into :targetShipment with following information:
     *
     * @param string $sourceShipmentReference
     * @param string $targetShipmentReference
     * @param TableNode $table
     */
    public function mergeProductsToShipment(string $sourceShipmentReference, string $targetShipmentReference, TableNode $table): void
    {
        $data = $table->getColumnsHash();
        $orderDetailQuantities = [];
        $sourceShipmentId = SharedStorage::getStorage()->get($sourceShipmentReference);
        $targetShipmentId = SharedStorage::getStorage()->get($targetShipmentReference);

        $getSourceShipmentProducts = $this->getQueryBus()->handle(
            new GetShipmentProducts($sourceShipmentId)
        );

        foreach ($getSourceShipmentProducts as $sourceShipmentProduct) {
            $orderDetail = new OrderDetail($sourceShipmentProduct->getOrderDetailId());
            foreach ($data as $value) {
                if ($orderDetail->product_name === $value['product_name']) {
                    $orderDetailQuantities[] = [
                        'id_order_detail' => $orderDetail->id,
                        'quantity' => $value['quantity'],
                    ];
                }
            }
        }

        $this->getCommandBus()->handle(
            new MergeProductsToShipment($sourceShipmentId, $targetShipmentId, $orderDetailQuantities)
        );
    }

    /**
     * @Then order :orderReference should get available shipments for product :productReference:
     */
    public function orderShouldGetAvailableShipmentsForSpecificProduct(string $orderReference, string $productReference, TableNode $table): void
    {
        $orderId = $this->referenceToId($orderReference);
        $data = $table->getColumnsHash();
        $orderDetailList = (new Order($orderId))->getOrderDetailList();
        $orderDetailsId = [];
        foreach ($orderDetailList as $orderDetail) {
            if ($orderDetail['product_name'] === $productReference) {
                $orderDetailsId[] = $orderDetail['id_order_detail'];
            }
        }

        $testAvailableShipmentForProduct = $this->getQueryBus()->handle(
            new ListAvailableShipments($orderId, $orderDetailsId)
        );

        for ($i = 0; $i < count($testAvailableShipmentForProduct); ++$i) {
            Assert::assertEquals($testAvailableShipmentForProduct[$i]->getShipmentName(), $data[$i]['shipment_name']);
            Assert::assertEquals($testAvailableShipmentForProduct[$i]->getHandleProduct(), (bool) $data[$i]['can_handle_merge']);
        }
    }
}
