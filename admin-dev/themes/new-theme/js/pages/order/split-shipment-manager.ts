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

  onSplitShipmentClick = (event: Event): void => {
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

  async refreshSplitShipmentForm(): Promise<void> {
    try {
      const response = await fetch(this.router.generate(this.refreshFormRoute, {
        orderId: this.orderId,
        shipmentId: this.shipmentId,
      }), {
        method: 'GET',
        headers: {
          'Content-Type': 'application/json',
        },
      });

      if (!response.ok) {
        throw new Error(await response.text());
      }

      const modalContainer = document.querySelector('#splitShipmentModalContainer');
      modalContainer!.innerHTML = await response.text();

      this.initCloseButtonModals();
    } catch (error) {
      console.error('Error while loading split shipment form:', error);
    }
  }

  initCloseButtonModals = () => {
    const modal = document.querySelector(OrderViewPageMap.splitShipmentModal);
    const closeButtons = modal?.querySelectorAll('[data-dismiss="modal"]');
    if (closeButtons && closeButtons.length > 0) {
      closeButtons.forEach(btn => {
        btn.removeEventListener('click', this.closeModal);
        btn.addEventListener('click', this.closeModal);
      })
    }
  }

  closeModal = () => {
    $(OrderViewPageMap.splitShipmentModal).modal('hide');
    this.shipmentId = null;
    this.orderId = null;

  }
}
