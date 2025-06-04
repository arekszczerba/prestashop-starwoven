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

namespace PrestaShop\PrestaShop\Adapter\Discount\Update;

use Doctrine\DBAL\Connection;
use PrestaShop\PrestaShop\Core\Domain\Discount\ProductRuleGroup;
use PrestaShop\PrestaShop\Core\Domain\Discount\ValueObject\DiscountId;

class DiscountConditionsUpdater
{
    public function __construct(
        private readonly Connection $connection,
        private readonly string $dbPrefix,
    ) {
    }

    public function update(
        DiscountId $discountId,
        ?int $minimumProductsQuantity = null,
    ): void {
        // todo: when other conditions are added we check that only one is provided
        // todo: always clean the other conditions so that only one remains after update
        if (null !== $minimumProductsQuantity) {
            $this->updateMinimalProductQuantity($discountId, $minimumProductsQuantity);
        }
    }

    /**
     * There is no field in the DB that handles a condition directly on the number of products in the cart
     * so we trick this condition by adding a product selection based on the root category (that contains
     * all the products).
     *
     * @param DiscountId $discountId
     * @param int $minimumProductsQuantity
     *
     * @return void
     */
    private function updateMinimalProductQuantity(DiscountId $discountId, int $minimumProductsQuantity): void
    {
        $this->applyDiscountProductRules($discountId, [
            new ProductRuleGroup(
                $minimumProductsQuantity,
                // No rules applied, means any product can match
                [],
            ),
        ]);
    }

    /**
     * @param DiscountId $discountId
     * @param ProductRuleGroup[] $productRuleGroups
     *
     * @return void
     */
    private function applyDiscountProductRules(
        DiscountId $discountId,
        array $productRuleGroups,
    ) {
        $this->cleanDiscountProductRules($discountId);

        foreach ($productRuleGroups as $productRuleGroup) {
            // First create group
            $this->connection->createQueryBuilder()
                ->insert($this->dbPrefix . 'cart_rule_product_rule_group')
                ->values([
                    'id_cart_rule' => $discountId->getValue(),
                    'quantity' => $productRuleGroup->getQuantity(),
                ])
                ->executeStatement()
            ;
            $productRuleGroupId = $this->connection->lastInsertId();

            // Then create all product rules associated to the group
            foreach ($productRuleGroup->getRules() as $productRule) {
                $this->connection->createQueryBuilder()
                    ->insert($this->dbPrefix . 'cart_rule_product_rule')
                    ->values([
                        'id_product_rule_group' => ':productRuleGroupId',
                        'type' => ':type',
                    ])
                    ->setParameter('productRuleGroupId', $productRuleGroupId)
                    ->setParameter('type', $productRule->getType()->value)
                    ->executeStatement()
                ;
                $productRuleId = $this->connection->lastInsertId();

                // Finally assign all item values to the product rule via a multi insert statement
                $productRuleValues = [];
                $checkedIds = [];
                foreach ($productRule->getItemIds() as $itemId) {
                    if (in_array($itemId, $checkedIds, true)) {
                        // Skip in case there are duplicates
                        continue;
                    }

                    $productRuleValues[] = sprintf('(%d, %d)', $productRuleId, $itemId);
                    $checkedIds[] = $itemId;
                }
                $this->connection->prepare(sprintf(
                    'INSERT INTO %s (id_product_rule, id_item) VALUES %s',
                    $this->dbPrefix . 'cart_rule_product_rule_value',
                    implode(',', $productRuleValues)
                )
                )->executeStatement();
            }
        }
    }

    private function cleanDiscountProductRules(DiscountId $discountId): void
    {
        // First delete all associated product rule groups
        $this->connection->createQueryBuilder()
            ->delete($this->dbPrefix . 'cart_rule_product_rule_group', 'prg')
            ->where('prg.id_cart_rule = :discountId')
            ->setParameter('discountId', $discountId->getValue())
            ->executeStatement()
        ;

        // Then clean orphan rows in the related tables
        $this->connection->prepare('
            DELETE prv FROM ' . $this->dbPrefix . 'cart_rule_product_rule AS pr
            LEFT JOIN ' . $this->dbPrefix . 'cart_rule_product_group AS prg ON prg.id_product_rule_group = pr.id_product_rule_group
            WHERE prg.id_product_rule_group = NULL
        ');

        $this->connection->prepare('
            DELETE prv FROM ' . $this->dbPrefix . 'cart_rule_product_rule_value AS prv
            LEFT JOIN ' . $this->dbPrefix . 'cart_rule_product_rule AS pr ON prv.id_product_rule = pr.id_product_rule
            WHERE pr.id_product_rule = NULL
        ');
    }
}
