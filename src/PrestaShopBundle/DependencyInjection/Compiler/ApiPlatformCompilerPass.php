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

namespace PrestaShopBundle\DependencyInjection\Compiler;

use PrestaShopBundle\ApiPlatform\Scopes\ApiResourceScopesExtractorFactory;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ApiPlatformCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $scopes = [];
        if ($container->hasParameter('api_platform.oauth.scopes')) {
            $scopes = $container->getParameter('api_platform.oauth.scopes');
        }

        // The service is not accessible during early compiler phase so build it manually
        $apiResourceScopesExtractor = ApiResourceScopesExtractorFactory::build(
            $container->getParameter('kernel.environment'),
            $container->getParameter('prestashop.module_dir'),
            $container->getParameter('prestashop.installed_modules'),
            $container->getParameter('prestashop.active_modules'),
            $container->getParameter('kernel.project_dir'),
        );
        foreach ($apiResourceScopesExtractor->getEnabledApiResourceScopes() as $apiResourceScope) {
            foreach ($apiResourceScope->getScopes() as $scope) {
                $scopes[$scope] = $scope;
            }
        }

        $container->setParameter('api_platform.oauth.scopes', $scopes);
    }
}
