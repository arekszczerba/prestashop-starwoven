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

namespace Tests\Integration\Behaviour\Features\Context\Domain\Discount;

use Behat\Gherkin\Node\TableNode;
use PHPUnit\Framework\Assert;
use PrestaShop\PrestaShop\Core\Domain\Discount\Command\UpdateDiscountConditionsCommand;
use PrestaShop\PrestaShop\Core\Domain\Discount\Query\GetDiscountForEditing;
use PrestaShop\PrestaShop\Core\Domain\Discount\QueryResult\DiscountForEditing;
use Tests\Integration\Behaviour\Features\Context\Domain\AbstractDomainFeatureContext;

class DiscountConditionsFeatureContext extends AbstractDomainFeatureContext
{
    /**
     * @When I update discount :discountReference with following conditions:
     *
     * @param string $discountReference
     * @param TableNode $tableNode
     *
     * @return void
     */
    public function updateDiscountCondition(string $discountReference, TableNode $tableNode): void
    {
        $command = new UpdateDiscountConditionsCommand($this->referenceToId($discountReference));

        $data = $tableNode->getRowsHash();
        if (isset($data['minimum_product_quantity'])) {
            $command->setMinimumProductsQuantity($data['minimum_product_quantity']);
        }

        $this->getCommandBus()->handle($command);
    }

    /**
     * @Then discount :discountReference should have the following product conditions:
     *
     * @param string $discountReference
     * @param TableNode $tableNode
     *
     * @return void
     */
    public function assertProductConditions(string $discountReference, TableNode $tableNode): void
    {
        /** @var DiscountForEditing $discountForEditing */
        $discountForEditing = $this->getQueryBus()->handle(
            new GetDiscountForEditing($this->getSharedStorage()->get($discountReference))
        );

        $conditionsData = $tableNode->getColumnsHash();
        $productConditions = $discountForEditing->getProductConditions();

        Assert::assertEquals(count($conditionsData), count($productConditions), sprintf('Expected %d conditions but got %d instead', count($conditionsData), count($productConditions)));
        foreach ($conditionsData as $index => $conditionData) {
            $productRuleGroup = $productConditions[$index];
            Assert::assertEquals((int) $conditionData['quantity'], $productRuleGroup->getQuantity(), sprintf('Expected quantity %d but got %d instead', (int) $conditionData['quantity'], $productRuleGroup->getQuantity()));
            Assert::assertEquals((int) $conditionData['rules_count'], count($productRuleGroup->getRules()), sprintf('Expected %d rules but got %d instead', (int) $conditionData['rules_count'], count($productRuleGroup->getRules())));
        }
    }

    /**
     * @Then discount :discountReference should have no product conditions
     *
     * @param string $discountReference
     *
     * @return void
     */
    public function assertNoProductConditions(string $discountReference): void
    {
        /** @var DiscountForEditing $discountForEditing */
        $discountForEditing = $this->getQueryBus()->handle(
            new GetDiscountForEditing($this->getSharedStorage()->get($discountReference))
        );

        Assert::assertEmpty($discountForEditing->getProductConditions(), 'Product conditions were found when none is expected');
    }
}
