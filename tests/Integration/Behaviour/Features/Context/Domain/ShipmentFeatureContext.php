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
use DateTime;
use Order;
use RuntimeException;
use PHPUnit\Framework\Assert;
use Tests\Integration\Behaviour\Features\Context\SharedStorage;
use PrestaShop\PrestaShop\Core\Domain\Shipment\Exception\ShipmentException;
use PrestaShop\PrestaShop\Core\Domain\Shipment\Query\GetOrderShipments;
use PrestaShop\PrestaShop\Core\Domain\Shipment\Command\AddShipmentCommand;
use PrestaShopBundle\Entity\ShipmentProduct;

class ShipmentFeatureContext extends AbstractDomainFeatureContext
{
    /**
     * @Given I add new shipment :shipmentReference for :orderReference
     *
     * @param TableNode $table
     */
    public function createShipmentUsingCommand(string $shipmentReference, string $orderReference)
    {
        $orderId = SharedStorage::getStorage()->get($orderReference);
        $order = new Order($orderId);
        $orderProducts = $order->getProducts();

        try {
            $shipmentId = $this->getCommandBus()->handle(
                new AddShipmentCommand(
                    (int) $order->id,
                    (int) $order->id_carrier,
                    (int) $order->id_address_delivery,
                    (float) $order->total_shipping_tax_excl,
                    (float) $order->total_shipping_tax_incl,
                    [],
                    "",
                    null,
                    null,
                    strtotime($order->delivery_date) === 'strtotime' ? null : new DateTime('now')
                )
            );
            SharedStorage::getStorage()->set('shipment1', (int) $shipmentId);
        } catch (ShipmentException $e) {
            $this->setLastException($e);
        }
    }

    /**
     * @Then the shipment :shipmentReference should linked to an order
     *
     * @param string $shipmentReference
     * @throws RuntimeException
     */
    public function orderHasShipment(string $shipmentReference)
    {
        $orderId = SharedStorage::getStorage()->get($shipmentReference);
        $shipments = $this->getQueryBus()->handle(
            new GetOrderShipments($orderId)
        );

        if (count($shipments) === 0) {
            $msg = "Order [" . $orderId . "] has no shipments";
            throw new RuntimeException($msg);
        }

        foreach ($shipments as $shipment) {
            if ($shipment->getOrderId() !== $orderId) {
                throw new RuntimeException("Shipment [" . $shipment->getId() . "] does not belong to order [" . $orderId . "]");
            }
        }

        return $shipments;
    }

    /**
     * @param string $references
     *
     * @return int[]
     */
    protected function referencesToIds(string $references): array
    {
        if (empty($references)) {
            return [];
        }

        $ids = [];
        foreach (explode(",", $references) as $reference) {
            $reference = trim($reference);

            if (!$this->getSharedStorage()->exists($reference)) {
                throw new RuntimeException(
                    sprintf(
                        "Reference %s does not exist in shared storage",
                        $reference
                    )
                );
            }

            $ids[] = $this->getSharedStorage()->get($reference);
        }

        return $ids;
    }
}
