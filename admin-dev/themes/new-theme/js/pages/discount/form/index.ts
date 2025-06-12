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

import PriceReductionManager from '@components/form/price-reduction-manager';
import FormFieldToggler, { SwitchEventData, ToggleType } from '@js/components/form/form-field-toggler';
import DiscountMap from '@pages/discount/discount-map';
import CreateFreeGiftDiscount from '@pages/discount/form/create-free-gift-discount';

$(() => {
  window.prestashop.component.initComponents(
    [
      'EventEmitter',
      'TranslatableInput',
      'ToggleChildrenChoice',
      'GeneratableInput',
      'FormFieldToggler',
    ],
  );

  const {eventEmitter} = window.prestashop.instance;

  new CreateFreeGiftDiscount(eventEmitter);

  new PriceReductionManager(
    DiscountMap.reductionTypeSelect,
    DiscountMap.includeTaxInput,
    DiscountMap.currencySelect,
    DiscountMap.reductionValueSymbol,
    DiscountMap.currencySelectContainer,
  );
  toggleCurrency();
  document.querySelector(DiscountMap.reductionTypeSelect)?.addEventListener('change', toggleCurrency);

  function toggleCurrency(): void {
    if ($(DiscountMap.reductionTypeSelect).val() === 'percentage') {
      $(DiscountMap.currencySelect).fadeOut();
    } else {
      $(DiscountMap.currencySelect).fadeIn();
    }
  }

  // Initialize the form field toggler for the discount usability mode
  new FormFieldToggler({
    disablingInputSelector: DiscountMap.usabilityModeSelectInput,
    targetSelector: DiscountMap.codeGeneratorInput,
    disableOnMatch: false,
    matchingValue: 'code',
    switchEvent: DiscountMap.discountUsabilityModeChangeEvent,
    toggleType: ToggleType.visibility,
  });
  // Listen to the discount usability mode change event,
  // we need to reset value of the code input if "auto" mode is selected@
  eventEmitter.on(DiscountMap.discountUsabilityModeChangeEvent, (event: SwitchEventData) => {
    if (event.targetSelector === DiscountMap.codeGeneratorInput && event.disable) {
      let input: HTMLInputElement|null = document.querySelector(DiscountMap.codeGeneratorInput + " input");
      if (input !== null) {
        input.value = '';
      }
    }
  });
});
