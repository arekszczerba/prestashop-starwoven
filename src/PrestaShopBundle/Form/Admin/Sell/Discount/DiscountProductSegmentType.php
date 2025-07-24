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

namespace PrestaShopBundle\Form\Admin\Sell\Discount;

use PrestaShopBundle\Form\Admin\Sell\Product\Description\ManufacturerType;
use PrestaShopBundle\Form\Admin\Type\CategoryChoiceTreeType;
use PrestaShopBundle\Form\Admin\Type\TranslatorAwareType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Constraints\When;

class DiscountProductSegmentType extends TranslatorAwareType
{
    public const CATEGORY = 'category';
    public const MANUFACTURER = 'manufacturer';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(self::MANUFACTURER, ManufacturerType::class, [
                'label' => $this->trans('Brand', 'Admin.Catalog.Feature'),
                'required' => false,
            ])
            ->add(self::CATEGORY, CategoryChoiceTreeType::class, [
                'label' => $this->trans('Category', 'Admin.Catalog.Feature'),
                'required' => false,
            ])
            ->add('quantity', IntegerType::class, [
                'label' => $this->trans('Minimum product quantity', 'Admin.Catalog.Feature'),
                'attr' => [
                    'class' => 'js-comma-transformer',
                ],
                'constraints' => [
                    new When(
                        expression: sprintf(
                            'this.getParent().getParent().get("children_selector").getData() === "%s"',
                            CartConditionsType::PRODUCT_SEGMENT,
                        ),
                        constraints: [
                            new NotBlank(),
                            new Type([
                                'type' => 'numeric',
                            ]),
                            new GreaterThanOrEqual([
                                'value' => 1,
                            ]),
                        ],
                    ),
                ],
            ])
        ;
    }
}
