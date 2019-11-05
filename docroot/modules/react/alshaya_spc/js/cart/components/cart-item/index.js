import React from 'react';

import CartConfigurableOption from '../cart-configurable-option';
import CartPromotion from '../cart-promotion';
import CartItemOOS from '../cart-item-oos';
import ItemLowQuantity from '../item-low-quantity';
import CartItemImage from '../cart-item-image';

export default class CartItem extends React.Component {

  render() {
    return <div>
      <div><CartItemImage img_data={this.props.item.extra_data.cart_image} /></div>
      <div><a href={Drupal.url.toAbsolute(this.props.item.link)}>{this.props.item.title}</a></div>
      <div>
        {this.props.item.configurable_values.map((key, val) =>
          <CartConfigurableOption label={key} />
        )}
      </div>
      <div>
        {this.props.item.promotions.map((key, val) =>
          <CartPromotion promo={key} />
        )}
      </div>
      <CartItemOOS in_stock={this.props.item.in_stock} />
      <ItemLowQuantity stock={this.props.item.stock} qty={this.props.item.qty} />
    </div>
  }

}
