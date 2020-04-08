import React from 'react';

import CheckoutConfigurableOption from '../../../utilities/checkout-configurable-option';
import CartPromotion from '../cart-promotion';
import CartItemOOS from '../cart-item-oos';
import CartItemError from '../cart-item-error';
import ItemLowQuantity from '../item-low-quantity';
import CheckoutItemImage from '../../../utilities/checkout-item-image';
import CartQuantitySelect from '../cart-quantity-select';
import { updateCartItemData } from '../../../utilities/update_cart';
import SpecialPrice from '../../../utilities/special-price';
import dispatchCustomEvent from '../../../utilities/events';

export default class CartItem extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      isItemError: false,
      errorMessage: null,
    };
  }

  componentDidMount() {
    document.addEventListener('spcCartItemError', this.handleCartItemError, false);
  }

  componentWillUnmount() {
    document.removeEventListener('spcCartItemError', this.handleCartItemError, false);
  }

  /**
   * Handle and show error on continue cart action.
   */
  handleCartItemError = (e) => {
    const { item: { sku } } = this.props;
    const errorMessage = e.detail;
    if (errorMessage !== null
      && errorMessage !== undefined
      && errorMessage[sku] !== undefined) {
      this.setState({
        isItemError: true,
        errorMessage: errorMessage[sku],
      });
    }
  };

  /**
   * Remove item from the cart.
   */
  removeCartItem = (sku, action, id) => {
    const cartData = updateCartItemData(action, sku, 0);
    // Adding class on remove button for showing progress when click.
    document.getElementById(`remove-item-${id}`).classList.add('loading');
    if (cartData instanceof Promise) {
      cartData.then((result) => {
        // Remove loading class.
        document.getElementById(`remove-item-${id}`).classList.remove('loading');
        const cartResult = result;
        // Refreshing mini-cart.
        const eventMiniCart = new CustomEvent('refreshMiniCart', { bubbles: true, detail: { data: () => cartResult } });
        document.dispatchEvent(eventMiniCart);

        let messageInfo = null;
        if (cartResult.error !== undefined) {
          messageInfo = {
            type: 'error',
            message: cartResult.error_message,
          };
        } else {
          messageInfo = {
            type: 'success',
            message: Drupal.t('The product has been removed from your cart.'),
          };
        }

        // Refreshing cart components.
        const eventCart = new CustomEvent('refreshCart', { bubbles: true, detail: { data: () => cartResult } });
        document.dispatchEvent(eventCart);

        // Trigger message.
        if (messageInfo !== null) {
          dispatchCustomEvent('spcCartMessageUpdate', messageInfo);
        }
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

    const { isItemError, errorMessage } = this.state;
    let OOSClass = '';
    if (inStock !== true) {
      OOSClass = 'error';
    }

    return (
      <div className="spc-cart-item" data-sku={sku}>
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
                <SpecialPrice
                  price={parseFloat(originalPrice)}
                  finalPrice={parseFloat(finalPrice)}
                />
              </div>
              {freeItem === true
              && <div>{Drupal.t('FREE')}</div>}
            </div>
            <div className="spc-product-attributes-wrapper">
              {configurableValues.map((key) => <CheckoutConfigurableOption key={`${sku}-${key.attribute_code}-${key.value}`} label={key} />)}
            </div>
          </div>
          <div className="spc-product-tile-actions">
            <button title={Drupal.t('remove this item')} type="button" id={`remove-item-${id}`} className={`spc-remove-btn ${OOSClass}`} onClick={() => { this.removeCartItem(sku, 'remove item', id); }}>{Drupal.t('remove')}</button>
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
          {promotions.map((key) => <CartPromotion key={`${key}-${sku}`} promo={key} sku={sku} link />)}
        </div>
        <div className="spc-cart-item-warning">
          <CartItemOOS inStock={inStock} />
          <ItemLowQuantity stock={stock} qty={qty} in_stock={inStock} />
        </div>
        <div className="spc-cart-item-alerts">
          <div className="spc-cart-promotion-dynamic-message-product" />
          {/* Dynamic promo labels buy 2 more items, free gifts labels,
           qty limit labels go here. Name the child component
           .spc-cart-item-alerts-item */}
        </div>
        {/* @Todo: Show OOS only once. */}
        {isItemError
          && <CartItemError errorMessage={errorMessage} />}
      </div>
    );
  }
}
