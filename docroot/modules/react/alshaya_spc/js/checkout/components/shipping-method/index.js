import React from 'react';

import PriceElement from "../../../utilities/special-price/PriceElement";
import {
  addShippingInCart, removeFullScreenLoader,
  showFullScreenLoader
} from "../../../utilities/checkout_util";

export default class ShippingMethod extends React.Component {

  constructor(props) {
    super(props);
    this.state = {
      'selectedOption': this.props.selected
    };
  }

  changeShippingMethod = (method) => {
    if (this.state.selectedOption === method.method_code) {
      return;
    }

    showFullScreenLoader();

    this.setState({
      selectedOption: method.method_code
    });

    document.getElementById('shipping-method-' + method.method_code).checked = true;

    var event = new CustomEvent('changeShippingMethod', {
      bubbles: true,
      detail: {
        data: method
      }
    });
    document.dispatchEvent(event);

    let data = {};
    if (window.drupalSettings.user.uid > 0) {
      data['address_id'] = this.props.cart.cart.shipping_address.customer_address_id;
      data['country_id'] = window.drupalSettings.country_code;
    }
    else {
      // @TODO: Do the same for anonymous users.
    }

    let cart_data = addShippingInCart('update shipping', data);
    this.props.refreshCart(cart_data);
    removeFullScreenLoader();
  };

  render () {
    let method = this.props.method;
    let price = Drupal.t('FREE');
    if (method.amount > 0) {
      price = <PriceElement amount={method.amount}/>
    }
  	return(
      <div className='shipping-method' onClick={() => this.changeShippingMethod(method)}>
      	<input
      	  id={'shipping-method-' + method.method_code}
      	  className={method.method_code}
      	  type='radio'
      	  defaultChecked={this.state.selectedOption === method.method_code}
      	  value={method.method_code}
      	  name='shipping-method' />

        <label className='radio-sim radio-label'>
          <span className='carrier-title'>{this.props.method.carrier_title}</span>
          <span className='method-title'>{this.props.method.method_title}</span>
          <span className='spc-price'>{price}</span>
        </label>
      </div>
    );
  }

}
