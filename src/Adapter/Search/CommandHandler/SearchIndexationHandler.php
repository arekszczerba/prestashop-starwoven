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

namespace PrestaShop\PrestaShop\Adapter\Search\CommandHandler;

use Exception;
use PrestaShop\PrestaShop\Core\CommandBus\Attributes\AsCommandHandler;
use PrestaShop\PrestaShop\Core\Domain\Search\Command\SearchIndexationCommand;
use PrestaShop\PrestaShop\Core\Domain\Search\CommandHandler\SearchIndexationHandlerInterface;
use PrestaShop\PrestaShop\Core\Domain\Search\Exception\SearchIndexationException;
use Search;
use Shop;

/**
 * Handles search indexation command.
 */
#[AsCommandHandler]
final class SearchIndexationHandler implements SearchIndexationHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function handle(SearchIndexationCommand $command): void
    {
        $shopConstraint = $command->getShopConstraint();
        $shopId = $shopConstraint->getShopId()?->getValue();
        $shopGroupId = $shopConstraint->getShopGroupId()?->getValue();
        $isFull = $command->isFull();
        $productId = $command->getProductId();

        try {
            if (null !== $shopId) {
                Shop::setContext(Shop::CONTEXT_SHOP, $shopId);
            } elseif (null !== $shopGroupId) {
                Shop::setContext(Shop::CONTEXT_GROUP, $shopGroupId);
            } else {
                Shop::setContext(Shop::CONTEXT_ALL);
            }

            if (!Search::indexation($isFull, $productId?->getValue())) {
                throw new SearchIndexationException('Search indexation failed');
            }
        } catch (Exception $e) {
            throw new SearchIndexationException('Search indexation failed', 0, $e);
        }
    }
}
