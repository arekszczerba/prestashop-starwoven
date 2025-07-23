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
import Router from '@components/router';
import OrderViewPageMap from './OrderViewPageMap';

export default class SplitShipmentManager {
  private refreshFormRoute = 'admin_orders_shipment_get_split_form';

  private shipmentId: number|null = null;

  private orderId: number|null = null;

  private router = new Router();

  private currentAbortController: AbortController | null = null;

  private debounceTimer: number|null = null;

  constructor() {
    this.initSplitShipmentEventHandler();
  }

  initSplitShipmentEventHandler(): void {
    const mainDiv = document.querySelector(OrderViewPageMap.mainDiv);

    if (!mainDiv) {
      throw new Error('impossible to retrieve main div of the page');
    }
    mainDiv.addEventListener('click', this.onSplitShipmentClick);
  }

  onSplitShipmentClick = async (event: Event): Promise<void> => {
    const target = event.target as HTMLElement;

    if (target && target.matches(OrderViewPageMap.showSplitShipmentModalBtn)) {

      if (!target.dataset.orderId) {
        throw new Error('impossible to retrieve order id');
      }
      this.orderId = Number(target.dataset.orderId);

      if (!target.dataset.shipmentId) {
        throw new Error('impossible to retrieve shipment id');
      }
      this.shipmentId = Number(target.dataset.shipmentId);

      await this.refreshSplitShipmentForm();
      $(OrderViewPageMap.splitShipmentModal).modal('show');
    }
  }

  async refreshSplitShipmentForm(products: {} = {}, carrier: number = 0): Promise<void> {
    try {
      if (this.currentAbortController) {
        this.currentAbortController.abort();
      }

      this.currentAbortController = new AbortController();
      const { signal } = this.currentAbortController;

      const refreshFormUrl = this.router.generate(this.refreshFormRoute, {
        orderId: this.orderId,
        shipmentId: this.shipmentId,
        products,
        carrier: carrier
      });

      const response = await fetch(refreshFormUrl, {
        method: 'GET',
        headers: {
          'Content-Type': 'application/json',
        },
        signal,
      });

      this.currentAbortController = null;

      if (!response.ok) {
        throw new Error(await response.text());
      }

      const formContainer = document.querySelector('#splitShipmentFormContainer');
      formContainer!.innerHTML = await response.text();

      this.initForm();
    } catch (error: unknown) {
      if (error instanceof Error && error.name === 'AbortError') {
        return;
      }
      console.error('Error while loading split shipment form:', error);
    }
  }

  get form(): HTMLFormElement {
    const form = document.forms.namedItem('split_shipment');
    if (!form) {
      throw new Error('form not found')
    }
    return form;
  }

  get submitButton(): HTMLButtonElement {
    const button = document.querySelector<HTMLButtonElement>('button[type="submit"][form="split_shipment"]');
    if (!button) {
      throw new Error('Submit button not found')
    }
    return button;
  }

  toggleSubmitButton(enable: number = 0) {
    this.submitButton.disabled = !enable;
  }

  initForm = () => {
    this.form.removeEventListener('change', this.onChangeForm);
    this.form.addEventListener('change', this.onChangeForm);
    const carrierSelector: HTMLSelectElement|null = this.form.querySelector('#split_shipment_carrier');
    this.toggleSubmitButton(Number(carrierSelector?.value))
  }

  onChangeForm = async () => {
    const formData = new FormData(this.form);

    const products: {[key: number]: { selected?: number; quantity?: number; }} = {}
    let currentCarrier: number = 0;
    const regexpKey = /split_shipment\[products\]\[(\d+)\]\[(selected|selected_quantity|order_detail_id)\]/;

    formData.forEach((value, key) => {
      const keyMatch = key.match(regexpKey);
      if (keyMatch) {
        const productIndex = Number(keyMatch[1]);
        const fieldName = keyMatch[2];
        products[productIndex] = {...(products[productIndex] ?? {}), [fieldName]: Number(value)}
      }
      if (key === 'split_shipment[carrier]') {
        currentCarrier = Number(value);
      }
    });

    if (this.debounceTimer) {
      clearTimeout(this.debounceTimer);
    }
    this.debounceTimer = window.setTimeout(() => {
      this.refreshSplitShipmentForm(products, currentCarrier);
      this.debounceTimer = null;
    }, 500);
  }
}
