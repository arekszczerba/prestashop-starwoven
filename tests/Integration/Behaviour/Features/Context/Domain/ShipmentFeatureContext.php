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
use Cart;
use PHPUnit\Framework\Assert;
use PrestaShop\PrestaShop\Core\Domain\Shipment\Query\GetOrderShipments;
use RuntimeException;
use Tests\Integration\Behaviour\Features\Context\SharedStorage;

class ShipmentFeatureContext extends AbstractDomainFeatureContext
{
    /**
     * @Then the order :orderReference should have the following shipments:
     *
     * @param string $shipmentNumber
     * @param TableNode $table
     *
     * @throws RuntimeException
     */
    public function verifyOrderShipment(string $orderReference, TableNode $table)
    {
        $data = $table->getRowsHash();
        $orderId = SharedStorage::getStorage()->get($orderReference);
        $carrierId = SharedStorage::getStorage()->get($data['id_carrier']);
        // get address id from cart
        $addressId = (new Cart(SharedStorage::getStorage()->get($data['id_address'])))->id_address_delivery;

        $shipments = $this->getQueryBus()->handle(
            new GetOrderShipments($orderId)
        );

        if (count($shipments) === 0) {
            $msg = 'Order [' . $orderId . '] has no shipments';
            throw new RuntimeException($msg);
        }

        foreach ($shipments as $shipment) {
            if ($shipment->getOrderId() !== $orderId) {
                throw new RuntimeException('Shipment [' . $shipment->getId() . '] does not belong to order [' . $orderId . ']');
            }

            Assert::assertEquals($shipment->getTrackingNumber(), $data['tracking_number']);
            Assert::assertEquals($shipment->getCarrierId(), $carrierId);
            Assert::assertEquals($shipment->getAddressId(), $addressId);
            Assert::assertEquals($shipment->getShippingCostTaxExcluded(), $data['shipping_cost_tax_excl']);
            Assert::assertEquals($shipment->getShippingCostTaxIncluded(), $data['shipping_cost_tax_incl']);
            SharedStorage::getStorage()->set($data['shipment'], $shipment);
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
        $shipment = SharedStorage::getStorage()->get($shipmentReference);

        $products = $shipment->getProducts();

        Assert::assertEquals($products[0]->getQuantity(), (int) $data[0]['quantity']);
        Assert::assertEquals($products[1]->getQuantity(), (int) $data[1]['quantity']);
        Assert::assertEquals($products[0]->getOrderDetailId(), (int) $data[0]['order_detail']);
        Assert::assertEquals($products[1]->getOrderDetailId(), (int) $data[1]['order_detail']);
    }
}
