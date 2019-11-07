import React from 'react';

import CartConfigurableOption from '../cart-configurable-option';
import CartPromotion from '../cart-promotion';
import CartItemOOS from '../cart-item-oos';
import ItemLowQuantity from '../item-low-quantity';
import CartItemImage from '../cart-item-image';

export default class CartItem extends React.Component {

  removeCartItem = () => {
    alert('product-removed');
  };

  render() {
    console.log(this.props.item);
    return (
      <div className="spc-cart-item">
        <div className="spc-product-tile">
          <div className="spc-product-image">
            <CartItemImage img_data={this.props.item.extra_data.cart_image} />
          </div>
          <div className="spc-product-container">
            <div className="spc-product-title-price">
              <div className="spc-product-title">
                <a href={Drupal.url.toAbsolute(this.props.item.link)}>{this.props.item.title}</a>
              </div>
              <div className="spc-product-price">{drupalSettings.alshaya_spc.currency_config.currency_code}{this.props.item.original_price}</div>
            </div>
            <div className="spc-product-attributes-wrapper">
              {this.props.item.configurable_values.map((key, val) =>
                <CartConfigurableOption label={key} />
              )}
            </div>
          </div>
          <div className="spc-product-tile-actions">
            <button className="spc-remove-btn" onClick={() => {this.removeCartItem()}}>{Drupal.t('remove')}</button>
            <div className="qty">
              <select>
                <option value="1">1</option>
                <option value="2">2</option>
                <option value="3">3</option>
                <option value="4">4</option>
              </select>
            </div>
          </div>
        </div>
        <div className="promotions">
          {this.props.item.promotions.map((key, val) =>
            <CartPromotion promo={key} />
          )}
        </div>
        <CartItemOOS in_stock={this.props.item.in_stock} />
        <ItemLowQuantity stock={this.props.item.stock} qty={this.props.item.qty} />
      </div>
    );
  }

}
