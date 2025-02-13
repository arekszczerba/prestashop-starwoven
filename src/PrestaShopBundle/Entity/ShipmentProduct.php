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

namespace PrestaShopBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table()
 * @ORM\Entity()
 */
class ShipmentProduct
{

    /**
     * @ORM\Column(name="shipment_product_id", type="integer")
     */
    private int $shipmentProductId;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="PrestaShopBundle\Entity\Shipment", inversedBy="products")
     * @ORM\JoinColumn(name="shipment_id", referencedColumnName="id_shipment", nullable=false)
     */
    private Shipment $shipmentId;

    /**
     * @ORM\Column(name="order_detail_id", type="integer")
     */
    private int $orderDetailId;

    /**
     * @ORM\Column(name="quantity", type="integer")
     */
    private int $quantity;

    public function getShipmentProductId(): int
    {
        return $this->shipmentProductId;
    }

    public function getShipment(): Shipment
    {
        return $this->shipmentId;
    }

    public function getOrderDetailId(): int
    {
        return $this->orderDetailId;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setShipmentProductId(int $shipmentProductId): self
    {
        $this->shipmentProductId = $shipmentProductId;
        return $this;
    }

    public function setShipment(Shipment $shipment): self
    {
        $this->shipmentId = $shipment;
        return $this;
    }

    public function setOrderDetailId(int $orderDetailId): self
    {
        $this->orderDetailId = $orderDetailId;
        return $this;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;
        return $this;
    }
}
