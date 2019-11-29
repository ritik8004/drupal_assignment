import React from 'react';

import CartConfigurableOption from '../cart-configurable-option';
import CartPromotion from '../cart-promotion';
import CartItemOOS from '../cart-item-oos';
import ItemLowQuantity from '../item-low-quantity';
import CartItemImage from '../cart-item-image';
import CartQuantitySelect from '../cart-quantity-select';
import {updateCartItemData} from '../../../utilities/update_cart';

export default class CartItem extends React.Component {

  /**
   * Remove item from the cart.
   */
  removeCartItem = (sku, action, id) => {
    // Adding class on remove button for showing progress when click.
    document.getElementById('remove-item-' + id).classList.add('loading');
    var cart_data = updateCartItemData(action, sku, 0);
    if (cart_data instanceof Promise) {
      // Removing class on remove button for showing progress when click.
      document.getElementById('remove-item-' + id).classList.remove('loading');
      cart_data.then((result) => {
        // Refreshing mini-cart.
        var event = new CustomEvent('refreshMiniCart', {bubbles: true, detail: { data: () => result }});
        document.dispatchEvent(event);

        if (result.error !== undefined) {
          result.message = {
            'type': 'error',
            'message': result.error_message
          }
        } else {
          result.message = {
            'type': 'success',
            'message': Drupal.t('The product has been removed from your cart.')
          }
        }

        // Refreshing cart components.
        var event = new CustomEvent('refreshCart', {bubbles: true, detail: { data: () => result }});
        document.dispatchEvent(event);
      });
    }
  };

  render() {
    const { currency_code } = drupalSettings.alshaya_spc.currency_config;
    const {title, link, stock, qty, in_stock, original_price, configurable_values, promotions, extra_data, sku, id } = this.props.item;

    return (
      <div className="spc-cart-item">
        <div className="spc-product-tile">
          <div className="spc-product-image">
            <CartItemImage img_data={extra_data.cart_image} />
          </div>
          <div className="spc-product-container">
            <div className="spc-product-title-price">
              <div className="spc-product-title">
                <a href={Drupal.url.toAbsolute(link)}>{title}</a>
              </div>
              <div className="spc-product-price">{currency_code}{original_price}</div>
            </div>
            <div className="spc-product-attributes-wrapper">
              {configurable_values.map((key, val) =>
                <CartConfigurableOption key={val} label={key} />
              )}
            </div>
          </div>
          <div className="spc-product-tile-actions">
            <button id={'remove-item-' + id} className="spc-remove-btn" onClick={() => {this.removeCartItem(sku, 'remove item', id)}}>{Drupal.t('remove')}</button>
            <div className="qty">
              <CartQuantitySelect qty={qty} stock={stock} sku={sku} />
            </div>
          </div>
        </div>
        <div className="spc-promotions">
          {promotions.map((key, val) =>
            <CartPromotion key={val} promo={key} />
          )}
        </div>
        <CartItemOOS in_stock={in_stock} />
        <ItemLowQuantity stock={stock} qty={qty} in_stock={in_stock} />
      </div>
    );
  }

}
