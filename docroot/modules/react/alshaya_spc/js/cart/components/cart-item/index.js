import React from 'react';

import CartConfigurableOption from '../cart-configurable-option';
import CartPromotion from '../cart-promotion';
import CartItemOOS from '../cart-item-oos';
import ItemLowQuantity from '../item-low-quantity';
import CartItemImage from '../cart-item-image';
import Select from 'react-select';
import {removeItemFromCart} from '../../../utilities/update_cart';

// @TODO: For demo only, qty values should be dynamic.
const options = [
  { value: '1', label: '1' },
  { value: '2', label: '2', isDisabled: true },
  { value: '3', label: '3' },
  { value: '4', label: '4' },
  { value: '5', label: '5' },
  { value: '6', label: '6' },
];

export default class CartItem extends React.Component {
  constructor(props) {
    super(props);
    this.selectRef = React.createRef();
  }

  /**
   * Remove item from the cart.
   */
  removeCartItem = (sku, action, id) => {
    // Adding class on remove button for showing progress when click.
    document.getElementById('remove-item-' + id).classList.add('loading');
    var cart_data = removeItemFromCart(action, sku);
    if (cart_data instanceof Promise) {
      // Removing class on remove button for showing progress when click.
      document.getElementById('remove-item-' + id).classList.remove('loading');
      cart_data.then((result) => {
        // Refreshing mini-cart.
        var event = new CustomEvent('refreshMiniCart', {bubbles: true, detail: { data: () => result }});
        document.dispatchEvent(event);

        // Refreshing cart components..
        var event = new CustomEvent('refreshCart', {bubbles: true, detail: { data: () => result }});
        document.dispatchEvent(event);
      });
    }
  };

  onMenuOpen = () => {
    this.selectRef.current.select.inputRef.closest('.spc-select').classList.add('open');
  };

  onMenuClose = () => {
    this.selectRef.current.select.inputRef.closest('.spc-select').classList.remove('open');
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
              <Select ref={this.selectRef} classNamePrefix="spcSelect" className="spc-select" onMenuOpen={this.onMenuOpen} onMenuClose={this.onMenuClose} options={options} defaultValue={options[0]} isSearchable={false} />
            </div>
          </div>
        </div>
        <div className="spc-promotions">
          {promotions.map((key, val) =>
            <CartPromotion key={val} promo={key} />
          )}
        </div>
        <CartItemOOS in_stock={in_stock} />
        <ItemLowQuantity stock={stock} qty={qty} />
      </div>
    );
  }

}
