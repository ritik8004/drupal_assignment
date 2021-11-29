import React from 'react';

import { updateCartItemData } from '../../../utilities/update_cart';
import SpecialPrice from '../../../utilities/special-price';
import dispatchCustomEvent from '../../../utilities/events';
import Notifications from '../../../cart/components/cart-item/components/Notifications';
import { getStorageInfo } from '../../../utilities/storage';
import validateCartResponse from '../../../utilities/validation_util';
import TrashIconSVG from '../../../svg-component/trash-icon-svg';
import AdvantageCardExcludedItem from '../../../cart/components/advantage-card';
import { customStockErrorMessage } from '../../../utilities/checkout_util';

export default class CartVirtualItem extends React.Component {
  componentDidUpdate() {
    Drupal.ajax.bindAjaxLinks(document.getElementById('spc-cart'));
  }

  /**
   * Remove item from the cart.
   */
  removeCartItem = (sku, action, id) => {
    // Adding class on remove button for showing progress when click.
    document.getElementById(`remove-item-${id}`).classList.add('loading');
    const afterCartUpdate = () => {
      // Remove loading class.
      document.getElementById(`remove-item-${id}`).classList.remove('loading');
    };
    this.triggerUpdateCart({
      action,
      sku,
      qty: 0,
      callback: afterCartUpdate,
      successMsg: Drupal.t('The product has been removed from your cart.'),
    });
  };

  /**
   * Trigger update cart api call.
   */
  triggerUpdateCart = ({
    action, sku, qty, successMsg, callback = null,
  }) => {
    const cartData = updateCartItemData(action, sku, qty);
    if (cartData instanceof Promise) {
      cartData.then((result) => {
        if (!validateCartResponse(result)) {
          return null;
        }

        if (callback !== null) {
          callback();
        }
        const cartResult = result;

        let messageInfo = null;
        if (cartResult.error !== undefined) {
          const errorMessage = customStockErrorMessage(cartResult);
          messageInfo = {
            type: 'error',
            message: errorMessage,
          };
        } else {
          messageInfo = {
            type: 'success',
            message: successMsg,
          };
        }

        let triggerRecommendedRefresh = false;
        const itemsLength = (cartResult.items !== undefined)
          ? Object.keys(cartResult.items).length
          : 0;
        if (cartResult.items !== undefined
          && itemsLength > 0) {
          // Trigger if item is removed.
          if (action === 'remove item') {
            triggerRecommendedRefresh = true;
          } else {
            const cartFromStorage = getStorageInfo();
            // If number of items in storage not matches with
            // what we get from mdc, we refresh recommended products.
            if (cartFromStorage !== null
              && cartFromStorage.cart !== undefined
              && cartFromStorage.cart.items !== undefined
              && itemsLength !== Object.keys(cartFromStorage.cart.items).length) {
              triggerRecommendedRefresh = true;
            }
          }
        }

        // Refreshing mini-cart.
        const eventMiniCart = new CustomEvent('refreshMiniCart', { bubbles: true, detail: { data: () => cartResult } });
        document.dispatchEvent(eventMiniCart);

        // Refreshing cart components.
        const eventCart = new CustomEvent('refreshCart', { bubbles: true, detail: { data: () => cartResult } });
        document.dispatchEvent(eventCart);

        // Trigger message.
        if (messageInfo !== null) {
          dispatchCustomEvent('spcCartMessageUpdate', messageInfo);
        }

        // Trigger recommended products refresh.
        if (triggerRecommendedRefresh) {
          dispatchCustomEvent('spcRefreshCartRecommendation', {
            items: cartResult.items,
          });
        }

        // If qty limit enabled.
        if (drupalSettings.quantity_limit_enabled) {
          const { skus, callable } = this.props;
          Object.entries(skus).forEach(([, productSku]) => {
            callable(productSku);
          });
        }

        return null;
      });
    }
  }

  render() {
    const {
      item: {
        sku,
        id,
        freeItem,
        title,
        price,
        finalPrice,
      },
      qtyLimit: animationOffset,
      couponCode,
      totalsItems,
    } = this.props;

    const animationDelayValue = `${0.3 + animationOffset}s`;

    return (
      <div
        className="spc-cart-item fadeInUp"
        style={{ animationDelay: animationDelayValue }}
        data-sku={sku}
      >
        <div className="spc-product-tile">
          <div className="spc-product-image">
            Cart Image
          </div>
          <div className="spc-product-container">
            <div className="spc-product-title-price">
              <div className="spc-product-title">
                {Drupal.t('Alshaya eGift card')}
              </div>
              <div className="spc-product-price">
                <SpecialPrice price={price} freeItem={freeItem} finalPrice={finalPrice} />
              </div>
            </div>
            <div className="spc-product-attributes-wrapper">
              <div className="spc-cart-product-attribute">
                <span className="spc-cart-product-attribute-label">{Drupal.t('Style:')}</span>
                <span className="spc-cart-product-attribute-value">{title}</span>
              </div>
              <div className="spc-cart-product-attribute">
                <span className="spc-cart-product-attribute-label">{Drupal.t('Send to:')}</span>
                <span className="spc-cart-product-attribute-value">address@gmail.com</span>
              </div>
              <div className="spc-cart-product-attribute">
                <span className="spc-cart-product-attribute-label">{Drupal.t('Message:')}</span>
                <span className="spc-cart-product-attribute-value">Happy new year my beast</span>
              </div>
            </div>
          </div>
          <div className="spc-product-tile-actions">
            <button
              title={Drupal.t('remove this item')}
              type="button"
              id={`remove-item-${id}`}
              className="spc-remove-btn"
              disabled={(couponCode.length === 0 && freeItem)}
              onClick={() => { this.removeCartItem(sku, 'remove item', id); }}
            >
              <TrashIconSVG />
            </button>
          </div>
        </div>
        <Notifications>
          <AdvantageCardExcludedItem type="warning" totalsItems={totalsItems} id={id} />
        </Notifications>
      </div>
    );
  }
}
