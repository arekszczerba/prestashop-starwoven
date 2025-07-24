<?php

namespace PrestaShop\PrestaShop\Adapter\Order\Repository;

use OrderDetail;
use PrestaShop\PrestaShop\Core\Domain\Order\Exception\OrderDetailNotFoundException;
use PrestaShop\PrestaShop\Core\Domain\Shipment\ValueObject\OrderDetailId;
use PrestaShop\PrestaShop\Core\Exception\CoreException;
use PrestaShop\PrestaShop\Core\Repository\AbstractObjectModelRepository;
use PrestaShopException;

class OrderDetailRepository extends AbstractObjectModelRepository
{
    /**
     * Gets legacy Order
     *
     * @param OrderDetailId $orderDetailId
     *
     * @return OrderDetail
     *
     * @throws CoreException
     */
    public function get(OrderDetailId $orderDetailId): OrderDetail
    {
        try {
            $orderDetail = new OrderDetail($orderDetailId->getValue());

            if ($orderDetail->id !== $orderDetailId->getValue()) {
                throw new OrderDetailNotFoundException($orderDetailId, sprintf('%s #%d was not found', OrderDetail::class, $orderDetailId->getValue()));
            }
        } catch (PrestaShopException $e) {
            throw new CoreException(
                sprintf(
                    'Error occurred when trying to get %s #%d [%s]',
                    OrderDetail::class,
                    $orderDetailId->getValue(),
                    $e->getMessage()
                ),
                0,
                $e
            );
        }

        return $orderDetail;
    }
}
