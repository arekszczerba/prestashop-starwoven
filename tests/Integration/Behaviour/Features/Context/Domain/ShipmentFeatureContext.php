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
use RuntimeException;
use Tests\Integration\Behaviour\Features\Context\SharedStorage;
use PrestaShop\PrestaShop\Core\Domain\Shipment\Exception\ShipmentException;
use PrestaShop\PrestaShop\Core\Domain\Shipment\Query\GetOrderShipmentsQuery;
use PrestaShop\PrestaShop\Core\Domain\Shipment\Command\AddShipmentCommand;

class ShipmentFeatureContext extends AbstractDomainFeatureContext
{
    /**
     * @Given I add new shipment with the following properties:
     *
     * @param TableNode $table
     */
    public function createShipmentUsingCommand(TableNode $table)
    {
        $data = $this->localizeByRows($table);

        try {
            $this->getCommandBus()->handle(
                new AddShipmentCommand(
                    (int) $data["order_id"],
                    (int) $data["carrier_id"],
                    (int) $data["delivery_address_id"],
                    (float) $data["shipping_cost_tax_excl"],
                    (float) $data["shipping_cost_tax_incl"],
                    [],
                    $data["tracking_number"],
                    empty($data["packed_at"]) ? null : $data["packed_at"],
                    empty($data["shipped_at"]) ? null : $data["shipped_at"],
                    empty($data["delivered_at"]) ? null : $data["delivered_at"]
                )
            );
        } catch (ShipmentException $e) {
            $this->setLastException($e);
        }
    }

    /**
     * @Then order :orderReference has shipment :shipment
     *
     * @param string $orderReference
     * @throws RuntimeException
     */
    public function orderHasShipment(string $orderReference)
    {
        $orderId = SharedStorage::getStorage()->get($orderReference);
        $shipments = $this->getQueryBus()->handle(
            new GetOrderShipmentsQuery($orderId)
        );

        if (count($shipments) === 0) {
            $msg = "Order [" . $orderId . "] has no shipments";
            throw new RuntimeException($msg);
        }

        // foreach ($shipments as $shipment) {
        //     if ($shipment->getOrderId() !== $orderId) {
        //         throw new RuntimeException("Shipment [" . $shipment->getId() . "] does not belong to order [" . $orderId . "]");
        //     }
        // }

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
