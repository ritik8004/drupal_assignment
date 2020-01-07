import React from 'react';

import CheckoutItemImage from '../../../utilities/checkout-item-image';
import CheckoutConfigurableOption from '../../../utilities/checkout-configurable-option';

export default class CheckoutCartItem extends React.Component {

  render() {
  	const {title, relative_link, configurable_values, extra_data } = this.props.item;
  	return (
      <div>
      	<div className="spc-product-image">
            <CheckoutItemImage img_data={extra_data.cart_image} />
        </div>
        <div>
   			<a href={Drupal.url(relative_link)}>{title}</a>
        </div>
        <div>
        	{configurable_values.map((key, val) =>
                <CheckoutConfigurableOption key={val} label={key} />
            )}
        </div>
      </div>
    );
  }

}
