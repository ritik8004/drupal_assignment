import React from 'react';

import { updateCartItemData } from '../../../utilities/update_cart';
import dispatchCustomEvent from '../../../../../js/utilities/events';
import Notifications from '../../../cart/components/cart-item/components/Notifications';
import validateCartResponse from '../../../utilities/validation_util';
import TrashIconSVG from '../../../svg-component/trash-icon-svg';
import AdvantageCardExcludedItem from '../../../cart/components/advantage-card';
import { customStockErrorMessage } from '../../../utilities/checkout_util';
import PriceElement from '../../../utilities/special-price/PriceElement';
import CheckoutItemImage from '../../../utilities/checkout-item-image';

export default class CartVirtualItem extends React.Component {
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

        return null;
      });
    }
  }

  render() {
    const {
      item: {
        sku, // sku of product.
        id, // qoute_id.
        title, // title of the product.
        price, // price of the product.
        egiftOptions, // other information of product.
        media, // Product image.
      },
      totalsItems, // totals in the api response consist of adv_card_applicable.
    } = this.props;
    // Egift card product image.
    const cartImage = {
      url: (media.length > 0) ? media : undefined,
      alt: title,
      title,
    };
    // Reciepient email.
    const recieptEmail = (typeof egiftOptions.hps_giftcard_recipient_email !== 'undefined')
      ? egiftOptions.hps_giftcard_recipient_email
      : '';
    // Gift card message.
    const giftCardMessage = (typeof egiftOptions.hps_giftcard_message !== 'undefined')
      ? egiftOptions.hps_giftcard_message
      : '';
    return (
      <div
        className="spc-cart-item fadeInUp"
        data-sku={sku}
      >
        <div className="spc-product-tile">
          <div className="spc-product-image">
            <CheckoutItemImage img_data={cartImage} />
          </div>
          <div className="spc-product-container">
            <div className="spc-product-title-price">
              <div className="spc-product-title">
                {Drupal.t('Alshaya eGift card', {}, { context: 'egift' })}
              </div>
              <div className="spc-product-price">
                <PriceElement amount={price} />
              </div>
            </div>
            <div className="spc-product-attributes-wrapper">
              <div className="spc-cart-product-attribute">
                <span className="spc-cart-product-attribute-label">{Drupal.t('Style:', {}, { context: 'egift' })}</span>
                <span className="spc-cart-product-attribute-value">{title}</span>
              </div>
              <div className="spc-cart-product-attribute">
                <span className="spc-cart-product-attribute-label">{Drupal.t('Send to:', {}, { context: 'egift' })}</span>
                <span className="spc-cart-product-attribute-value">{ recieptEmail }</span>
              </div>
              <div className="spc-cart-product-attribute">
                <span className="spc-cart-product-attribute-label">{Drupal.t('Message:', {}, { context: 'egift' })}</span>
                <span className="spc-cart-product-attribute-value">{ giftCardMessage }</span>
              </div>
            </div>
          </div>
          <div className="spc-product-tile-actions">
            <button
              title={Drupal.t('remove this item')}
              type="button"
              id={`remove-item-${id}`}
              className="spc-remove-btn"
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
