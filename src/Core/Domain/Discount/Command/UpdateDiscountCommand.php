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

use DateTimeImmutable;
use PrestaShop\Decimal\DecimalNumber;
use PrestaShop\PrestaShop\Core\Domain\Currency\ValueObject\CurrencyId;
use PrestaShop\PrestaShop\Core\Domain\Customer\ValueObject\CustomerId;
use PrestaShop\PrestaShop\Core\Domain\Discount\Exception\DiscountConstraintException;
use PrestaShop\PrestaShop\Core\Domain\Discount\ValueObject\DiscountId;
use PrestaShop\PrestaShop\Core\Domain\Exception\DomainConstraintException;
use PrestaShop\PrestaShop\Core\Domain\Language\ValueObject\LanguageId;
use PrestaShop\PrestaShop\Core\Domain\Product\Combination\Exception\CombinationConstraintException;
use PrestaShop\PrestaShop\Core\Domain\Product\Combination\ValueObject\CombinationId;
use PrestaShop\PrestaShop\Core\Domain\Product\Combination\ValueObject\CombinationIdInterface;
use PrestaShop\PrestaShop\Core\Domain\Product\Combination\ValueObject\NoCombinationId;
use PrestaShop\PrestaShop\Core\Domain\Product\Exception\ProductConstraintException;
use PrestaShop\PrestaShop\Core\Domain\Product\ValueObject\ProductId;
use PrestaShop\PrestaShop\Core\Domain\ValueObject\Money;

class UpdateDiscountCommand
{
    private ?array $localizedNames = null;
    private ?int $priority = null;
    private ?bool $active = null;
    private ?DateTimeImmutable $validFrom = null;
    private ?DateTimeImmutable $validTo = null;
    private ?int $totalQuantity = null;
    private ?int $quantityPerUser = null;
    private ?string $description = null;
    private ?string $code = null;
    private ?CustomerId $customerId = null;
    private ?bool $highlightInCart = null;
    private ?bool $allowPartialUse = null;
    private ?DecimalNumber $percentDiscount = null;
    private ?Money $amountDiscount = null;
    private ?ProductId $productId = null;
    private ?CombinationIdInterface $combinationId = null;
    private ?int $reductionProduct = null;
    private DiscountId $discountId;
    private ?bool $freeShipping = null;

    public function __construct(int $discountId)
    {
        $this->discountId = new DiscountId($discountId);
    }

    /**
     * @param array<int, string> $localizedNames
     */
    public function setLocalizedNames(?array $localizedNames): self
    {
        if (null === $localizedNames) {
            $this->localizedNames = null;
        }
        foreach ($localizedNames as $languageId => $name) {
            $this->localizedNames[(new LanguageId($languageId))->getValue()] = $name;
        }

        return $this;
    }

    /**
     * @return array<int, string>|null
     */
    public function getLocalizedNames(): ?array
    {
        return $this->localizedNames;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(?bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function getValidFrom(): ?DateTimeImmutable
    {
        return $this->validFrom;
    }

    public function getValidTo(): ?DateTimeImmutable
    {
        return $this->validTo;
    }

    /**
     * @throws DiscountConstraintException
     */
    public function setValidityDateRange(?DateTimeImmutable $from, ?DateTimeImmutable $to): self
    {
        if (null === $from || null === $to) {
            throw new DiscountConstraintException('Cannot validate range if one of properties is null', DiscountConstraintException::INVALID_DATE_FROM_OR_DATE_TO);
        }
        $this->assertDateRangeIsValid($from, $to);
        $this->validFrom = $from;
        $this->validTo = $to;

        return $this;
    }

    public function getPriority(): ?int
    {
        return $this->priority;
    }

    /**
     * @throws DiscountConstraintException
     */
    public function setPriority(?int $priority): self
    {
        if (null === $priority || 0 >= $priority) {
            throw new DiscountConstraintException(
                sprintf('Invalid discount priority "%s". Must be a positive integer.', $priority),
                DiscountConstraintException::INVALID_PRIORITY
            );
        }

        $this->priority = $priority;

        return $this;
    }

    public function getTotalQuantity(): ?int
    {
        return $this->totalQuantity;
    }

    public function isHighlightInCart(): ?bool
    {
        return $this->highlightInCart;
    }

    public function setHighlightInCart(?bool $highlightInCart): void
    {
        $this->highlightInCart = $highlightInCart;
    }

    /**
     * @throws DiscountConstraintException
     */
    public function setTotalQuantity(?int $quantity): self
    {
        if (null === $quantity || 0 > $quantity) {
            throw new DiscountConstraintException(sprintf('Quantity cannot be lower than zero, %d given', $quantity), DiscountConstraintException::INVALID_QUANTITY);
        }

        $this->totalQuantity = $quantity;

        return $this;
    }

    public function getQuantityPerUser(): ?int
    {
        return $this->quantityPerUser;
    }

    /**
     * @throws DiscountConstraintException
     */
    public function setQuantityPerUser(?int $quantity): self
    {
        if (null === $quantity || 0 > $quantity) {
            throw new DiscountConstraintException(sprintf('Quantity per user cannot be lower than zero, %d given', $quantity), DiscountConstraintException::INVALID_QUANTITY_PER_USER);
        }

        $this->quantityPerUser = $quantity;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function allowPartialUse(): ?bool
    {
        return $this->allowPartialUse;
    }

    public function setAllowPartialUse(?bool $allow): self
    {
        $this->allowPartialUse = $allow;

        return $this;
    }

    public function getCustomerId(): ?CustomerId
    {
        return $this->customerId;
    }

    public function setCustomerId(?int $customerId): self
    {
        if (null === $customerId) {
            return $this;
        }
        $this->customerId = new CustomerId($customerId);

        return $this;
    }

    public function getPercentDiscount(): ?DecimalNumber
    {
        return $this->percentDiscount;
    }

    public function setPercentDiscount(?DecimalNumber $percentDiscount): self
    {
        if (null === $percentDiscount) {
            return $this;
        }
        $this->percentDiscount = $percentDiscount;

        return $this;
    }

    public function getAmountDiscount(): ?Money
    {
        return $this->amountDiscount;
    }

    /**
     * @throws DomainConstraintException|DiscountConstraintException
     */
    public function setAmountDiscount(?DecimalNumber $amountDiscount, ?CurrencyId $currencyId, ?bool $taxIncluded): self
    {
        if (null === $amountDiscount || null === $taxIncluded || null === $currencyId) {
            return $this;
        }
        if ($amountDiscount->isLowerThanZero()) {
            throw new DiscountConstraintException(sprintf('Money amount cannot be lower than zero, %s given', $amountDiscount), DiscountConstraintException::INVALID_DISCOUNT_VALUE_CANNOT_BE_NEGATIVE);
        }

        $this->amountDiscount = new Money($amountDiscount, $currencyId, $taxIncluded);

        return $this;
    }

    public function getProductId(): ?ProductId
    {
        return $this->productId;
    }

    /**
     * @throws ProductConstraintException
     */
    public function setProductId(?int $productId): self
    {
        if (null === $productId) {
            return $this;
        }
        $this->productId = new ProductId($productId);

        return $this;
    }

    public function getCombinationId(): ?CombinationIdInterface
    {
        return $this->combinationId;
    }

    /**
     * @throws CombinationConstraintException
     */
    public function setCombinationId(?int $combinationId): self
    {
        if (null === $combinationId) {
            return $this;
        }
        if (NoCombinationId::NO_COMBINATION_ID === $combinationId) {
            $this->combinationId = new NoCombinationId();
        } else {
            $this->combinationId = new CombinationId($combinationId);
        }

        return $this;
    }

    public function getReductionProduct(): ?int
    {
        return $this->reductionProduct;
    }

    /**
     * @param int $reductionProduct
     *
     * @return $this
     *
     * This can have several values
     *  0 => The discount is not a Product discount
     * -1 => The discounted product is the cheapest of the cart
     * -2 => The discount is applied on a selection of product // this case is not yet handled.
     * >0 => The productId of the discounted product
     */
    public function setReductionProduct(?int $reductionProduct): self
    {
        $this->reductionProduct = $reductionProduct;

        return $this;
    }

    public function getDiscountId(): DiscountId
    {
        return $this->discountId;
    }

    public function isFreeShipping(): ?bool
    {
        return $this->freeShipping;
    }

    public function setFreeShipping(?bool $freeShipping): self
    {
        $this->freeShipping = $freeShipping;

        return $this;
    }

    /**
     * @throws DiscountConstraintException
     */
    private function assertDateRangeIsValid(DateTimeImmutable $dateFrom, DateTimeImmutable $dateTo): void
    {
        if ($dateFrom > $dateTo) {
            throw new DiscountConstraintException('Date from cannot be greater than date to.', DiscountConstraintException::DATE_FROM_GREATER_THAN_DATE_TO);
        }
    }
}
