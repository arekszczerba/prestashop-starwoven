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

namespace Tests\Unit\Adapter\Routes;

use PHPUnit\Framework\TestCase;
use PrestaShop\PrestaShop\Adapter\Routes\RouteValidator;
use Dispatcher;
use Validate;


class RouteValidatorTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        // Mock Validate static method
        $validateMock = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['isRoutePattern'])
            ->getMock();
        $validateMock::staticExpects($this->any())
            ->method('isRoutePattern')
            ->willReturnCallback(function ($pattern) {
                // Accept only patterns with {id} or {rewrite}
                return preg_match('/\{[a-zA-Z0-9_]+\}/', $pattern) === 1;
            });

        // Replace Validate with our mock
        Validate::$instance = $validateMock;
    }

    public function tearDown(): void
    {
        parent::tearDown();
        // Clean up static mock
        Validate::$instance = null;
    }

    public function testIsRoutePatternReturnsTrueForValidPattern()
    {
        $validator = new RouteValidator();
        $this->assertTrue($validator->isRoutePattern('category/{id}-{rewrite}'));
    }

    public function testIsRoutePatternReturnsFalseForInvalidPattern()
    {
        $validator = new RouteValidator();
        $this->assertFalse($validator->isRoutePattern('category/id-rewrite'));
    }

    public function testDoesRouteContainsRequiredKeywordsCallsIsRouteValid()
    {
        $validator = $this->getMockBuilder(RouteValidator::class)
            ->onlyMethods(['isRouteValid'])
            ->getMock();

        $validator->expects($this->once())
            ->method('isRouteValid')
            ->with('category_rule', 'category/{id}-{rewrite}')
            ->willReturn([]);

        $validator->doesRouteContainsRequiredKeywords('category_rule', 'category/{id}-{rewrite}');
    }

    /**
     * @dataProvider routeValidationProvider
     */
    public function testIsRouteValid($routeId, $rule, $validateReturn, $expected)
    {
        $dispatcherMock = $this->getMockBuilder(Dispatcher::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['validateRoute'])
            ->getMock();

        $dispatcherMock->expects($this->once())
            ->method('validateRoute')
            ->with($routeId, $rule, $this->anything())
            ->willReturnCallback(function ($routeId, $rule, &$errors) use ($validateReturn) {
                $errors = $validateReturn['errors'];
                return $validateReturn['result'];
            });

        // Replace Dispatcher::getInstance() with our mock
        Dispatcher::setInstanceForTesting($dispatcherMock);

        $validator = new RouteValidator();
        $result = $validator->isRouteValid($routeId, $rule);

        $this->assertEquals($expected, $result);

        // Clean up static instance
        Dispatcher::setInstanceForTesting(null);
    }

    public function routeValidationProvider()
    {
        return [
            // Valid route
            [
                'category_rule',
                'category/{id}-{rewrite}',
                ['result' => true, 'errors' => []],
                [],
            ],
            // Missing keyword
            [
                'category_rule',
                'category/{id}',
                ['result' => false, 'errors' => ['missing' => ['rewrite'], 'unknown' => []]],
                ['missing' => ['rewrite'], 'unknown' => []],
            ],
            // Unknown keyword
            [
                'category_rule',
                'category/{id}-{rewrite}-{foo}',
                ['result' => false, 'errors' => ['missing' => [], 'unknown' => ['foo']]],
                ['missing' => [], 'unknown' => ['foo']],
            ],
            // Both missing and unknown
            [
                'category_rule',
                'category/{id}-{foo}',
                ['result' => false, 'errors' => ['missing' => ['rewrite'], 'unknown' => ['foo']]],
                ['missing' => ['rewrite'], 'unknown' => ['foo']],
            ],
            // Route id not found
            [
                'not_existing_rule',
                'category/{id}-{rewrite}',
                ['result' => false, 'errors' => ['missing' => [], 'unknown' => []]],
                ['missing' => [], 'unknown' => []],
            ],
        ];
    }
}
