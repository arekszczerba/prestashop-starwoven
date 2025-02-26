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

namespace PrestaShop\PrestaShop\Adapter\Shipment\CommandHandler;

use PrestaShop\PrestaShop\Core\CommandBus\Attributes\AsCommandHandler;
use PrestaShop\PrestaShop\Core\Domain\Shipment\Command\AddShipmentCommand;
use PrestaShop\PrestaShop\Core\Domain\Shipment\CommandHandler\AddShipmentHandlerInterface;
use PrestaShop\PrestaShop\Core\Domain\Shipment\Exception\CannotAddShipmentException;
use PrestaShopBundle\Entity\Repository\ShipmentRepository;
use PrestaShopBundle\Entity\Shipment;
use PrestaShopBundle\Entity\ShipmentProduct;

#[AsCommandHandler]
class AddShipmentHandler implements AddShipmentHandlerInterface
{
    public function __construct(
        private readonly ShipmentRepository $repository,
    ) {
    }

    public function handle(AddShipmentCommand $command): void
    {
        $shipment = new Shipment();
        $shipment->setOrderId($command->getOrderId());
        $shipment->setCarrierId($command->getCarrierId());
        $shipment->setTrakingNumber($command->getTrackingNumber());
        $shipment->setDeliveryAddressId($command->getDeliveryAddressId());
        $shipment->setShippingCostTaxExcluded($command->getShippingCostTaxExcluded());
        $shipment->setShippingCostTaxIncluded($command->getShippingCostTaxIncluded());
        $shipment->setProducts($command->getProducts());

        if ($command->getPackedAt() !== null) {
            $shipment->setPackedAt($command->getPackedAt());
        }

        if ($command->getShippedAt() !== null) {
            $shipment->setShippedAt($command->getShippedAt());
        }

        if ($command->getDeliveredAt() !== null) {
            $shipment->setDeliveredAt($command->getDeliveredAt());
        }

        try {
            $this->repository->save($shipment);
        } catch (\Throwable $e) {
           throw new CannotAddShipmentException("An error occured while creating shipment", 0, $e);
        }
    }
}
