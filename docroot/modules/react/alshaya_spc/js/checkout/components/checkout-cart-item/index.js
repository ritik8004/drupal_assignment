import React from 'react';

import CheckoutItemImage from '../../../utilities/checkout-item-image';
import CheckoutConfigurableOption from '../../../utilities/checkout-configurable-option';
import SpecialPrice from '../../../utilities/special-price';

export default class CheckoutCartItem extends React.Component {

  render() {
  	const {title, relative_link, configurable_values, extra_data, original_price, final_price } = this.props.item;
  	return (
      <div className="product-item">
      	<div className="spc-product-image">
            <CheckoutItemImage img_data={extra_data.cart_image} />
        </div>
        <div className="spc-product-meta-data">
          <div className="spc-product-title-price">
            <div className="spc-product-title">
              <a href={Drupal.url(relative_link)}>{title}</a>
            </div>
            <div className="spc-product-price">
              <SpecialPrice price={original_price} final_price={final_price} />
            </div>
          </div>
          <div className="spc-product-attributes">
            { configurable_values.map((key, val) => <CheckoutConfigurableOption key={val} label={key} />) }
          </div>
        </div>
      </div>
    );
  }

}
