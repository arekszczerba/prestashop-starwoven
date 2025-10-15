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

namespace PrestaShop\PrestaShop\Core\Form\ChoiceProvider;

use PrestaShop\PrestaShop\Core\Form\FormChoiceAttributeProviderInterface;
use PrestaShop\PrestaShop\Core\Form\FormChoiceFormatter;
use PrestaShop\PrestaShop\Core\Form\FormChoiceProviderInterface;
use PrestaShopBundle\Entity\Repository\AttributeGroupRepository;

/**
 * Class AttributeGroupChoiceProvider provides attribute group choices and choices attributes.
 */
final class AttributeGroupChoiceProvider implements FormChoiceProviderInterface, FormChoiceAttributeProviderInterface
{
    /**
     * @var array
     */
    private $attributeGroups;

    /**
     * @var array
     */
    private $attributeGroupsChoiceAttributes;

    /**
     * @param AttributeGroupRepository $attributeGroupRepository
     * @param int $langId
     * @param int $shopId
     */
    public function __construct(
        private readonly AttributeGroupRepository $attributeGroupRepository,
        private int $langId,
        private int $shopId,
    ) {
    }

    /**
     * Get attribute groups choices.
     *
     * @return array
     */
    public function getChoices()
    {
        return FormChoiceFormatter::formatFormChoices(
            $this->getAttributeGroups(),
            'attributeGroupId',
            'attributeGroupName'
        );
    }

    /**
     * Get attribute groups choices attributes.
     *
     * @return array
     */
    public function getChoicesAttributes()
    {
        if (null === $this->attributeGroupsChoiceAttributes) {
            $attributeGroups = $this->getAttributeGroups();

            $this->attributeGroupsChoiceAttributes = [];

            foreach ($attributeGroups as $attributeGroup) {
                if ($attributeGroup['attributeGroupisColorGroup']) {
                    $this->attributeGroupsChoiceAttributes[$attributeGroup['attributeGroupId']]['data-isColorGroup'] = $attributeGroup['attributeGroupId'];
                }
            }
        }

        return $this->attributeGroupsChoiceAttributes;
    }

    /**
     * Get attribute groups.
     *
     * @return array
     */
    private function getAttributeGroups()
    {
        if (null === $this->attributeGroups) {
            $this->attributeGroups = $this->attributeGroupRepository->findByLangAndShop($this->langId, $this->shopId);
        }

        if (empty($this->attributeGroups)) {
            throw new \RuntimeException('No attribute groups available.');
        }
        return $this->attributeGroups;
    }
}
