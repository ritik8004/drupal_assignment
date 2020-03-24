import React from 'react';

import CheckoutConfigurableOption from '../../../utilities/checkout-configurable-option';
import CartPromotion from '../cart-promotion';
import CartItemOOS from '../cart-item-oos';
import ItemLowQuantity from '../item-low-quantity';
import CheckoutItemImage from '../../../utilities/checkout-item-image';
import CartQuantitySelect from '../cart-quantity-select';
import { updateCartItemData } from '../../../utilities/update_cart';
import SpecialPrice from '../../../utilities/special-price';

export default class CartItem extends React.Component {
  /**
   * Remove item from the cart.
   */
  removeCartItem = (sku, action, id) => {
    const cartData = updateCartItemData(action, sku, 0);
    // Adding class on remove button for showing progress when click.
    document.getElementById(`remove-item-${id}`).classList.add('loading');
    if (cartData instanceof Promise) {
      cartData.then((result) => {
        const cartResult = result;
        // Refreshing mini-cart.
        const eventMiniCart = new CustomEvent('refreshMiniCart', { bubbles: true, detail: { data: () => cartResult } });
        document.dispatchEvent(eventMiniCart);

        if (cartResult.error !== undefined) {
          cartResult.message = {
            type: 'error',
            message: cartResult.error_message,
          };
        } else {
          cartResult.message = {
            type: 'success',
            message: Drupal.t('The product has been removed from your cart.'),
          };
        }

        // Refreshing cart components.
        const eventCart = new CustomEvent('refreshCart', { bubbles: true, detail: { data: () => cartResult } });
        document.dispatchEvent(eventCart);
      });
    }
  };

  render() {
    const {
      item: {
        title,
        relative_link: relativeLink,
        stock,
        qty,
        in_stock: inStock,
        original_price: originalPrice,
        configurable_values: configurableValues,
        promotions,
        extra_data: extraData,
        sku,
        id,
        final_price: finalPrice,
        free_item: freeItem,
      },
    } = this.props;

    return (
      <div className="spc-cart-item">
        <div className="spc-product-tile">
          <div className="spc-product-image">
            <CheckoutItemImage img_data={extraData.cart_image} />
          </div>
          <div className="spc-product-container">
            <div className="spc-product-title-price">
              <div className="spc-product-title">
                <a href={Drupal.url(relativeLink)}>{title}</a>
              </div>
              <div className="spc-product-price">
                <SpecialPrice price={originalPrice} final_price={finalPrice} />
              </div>
              {freeItem === true
              && <div>{Drupal.t('FREE')}</div>}
            </div>
            <div className="spc-product-attributes-wrapper">
              {configurableValues.map((key) => <CheckoutConfigurableOption key={`${key}-${Math.floor(Math.random() * 99)}`} label={key} />)}
            </div>
          </div>
          <div className="spc-product-tile-actions">
            <button title={Drupal.t('remove this item')} type="button" id={`remove-item-${id}`} className="spc-remove-btn" onClick={() => { this.removeCartItem(sku, 'remove item', id); }}>{Drupal.t('remove')}</button>
            <div className="qty">
              <div className="qty-loader-placeholder" />
              <CartQuantitySelect
                qty={qty}
                stock={stock}
                sku={sku}
                is_disabled={!inStock || freeItem}
              />
            </div>
          </div>
        </div>
        <div className="spc-promotions">
          {promotions.map((key) => <CartPromotion key={`${key}-${Math.floor(Math.random() * 99)}`} promo={key} link />)}
        </div>
        <CartItemOOS inStock={inStock} />
        <ItemLowQuantity stock={stock} qty={qty} in_stock={inStock} />
      </div>
    );
  }
}
