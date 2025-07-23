<?php

namespace PrestaShop\PrestaShop\Adapter\Order\Repository;

use OrderDetail;
use PrestaShop\PrestaShop\Core\Domain\Shipment\ValueObject\OrderDetailsId;
use PrestaShop\PrestaShop\Core\Exception\CoreException;
use PrestaShop\PrestaShop\Core\Repository\AbstractObjectModelRepository;
use PrestaShopException;

class OrderDetailRepository extends AbstractObjectModelRepository
{
    /**
     * Gets legacy Order
     *
     * @param OrderDetailsId $orderDetailsId
     *
     * @return OrderDetail
     *
     * @throws CoreException
     */
    public function get(OrderDetailsId $orderDetailsId): OrderDetail
    {
        try {
            $orderDetail = new OrderDetail($orderDetailsId->getValue());

            if ($orderDetail->id !== $orderDetailsId->getValue()) {
                throw new OrderDetailNotFoundException($orderDetailsId, sprintf('%s #%d was not found', OrderDetail::class, $orderDetailsId->getValue()));
            }
        } catch (PrestaShopException $e) {
            throw new CoreException(
                sprintf(
                    'Error occurred when trying to get %s #%d [%s]',
                    Order::class,
                    $orderDetailsId->getValue(),
                    $e->getMessage()
                ),
                0,
                $e
            );
        }

        return $orderDetail;
    }
}
