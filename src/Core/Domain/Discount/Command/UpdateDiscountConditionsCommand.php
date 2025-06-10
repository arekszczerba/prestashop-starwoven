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

namespace PrestaShop\PrestaShop\Core\Domain\Discount\Command;

use PrestaShop\PrestaShop\Core\Domain\Discount\Exception\DiscountConstraintException;
use PrestaShop\PrestaShop\Core\Domain\Discount\ProductRuleGroup;
use PrestaShop\PrestaShop\Core\Domain\Discount\ValueObject\DiscountId;

class UpdateDiscountConditionsCommand
{
    private DiscountId $discountId;

    private ?int $minimumProductsQuantity = null;

    private ?array $productConditions = null;

    public function __construct(int $discountId)
    {
        $this->discountId = new DiscountId($discountId);
    }

    public function getDiscountId(): DiscountId
    {
        return $this->discountId;
    }

    public function getMinimumProductsQuantity(): ?int
    {
        return $this->minimumProductsQuantity;
    }

    public function setMinimumProductsQuantity(int $minimumProductsQuantity): self
    {
        if ($minimumProductsQuantity < 0) {
            throw new DiscountConstraintException('Minimum products quantity must be greater than 0', DiscountConstraintException::INVALID_MINIMUM_PRODUCT_QUANTITY);
        }

        $this->minimumProductsQuantity = $minimumProductsQuantity;

        return $this;
    }

    public function getProductConditions(): ?array
    {
        return $this->productConditions;
    }

    /**
     * @param ProductRuleGroup[] $productConditions
     *
     * @return self
     *
     * @throws DiscountConstraintException
     */
    public function setProductConditions(array $productConditions): self
    {
        foreach ($productConditions as $productCondition) {
            if (!$productCondition instanceof ProductRuleGroup) {
                throw new DiscountConstraintException(sprintf('Product conditions must be an array of %s', ProductRuleGroup::class), DiscountConstraintException::INVALID_PRODUCTS_CONDITIONS);
            }
        }

        $this->productConditions = $productConditions;

        return $this;
    }
}
