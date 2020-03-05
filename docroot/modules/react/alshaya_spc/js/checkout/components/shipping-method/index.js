import React from 'react';

import PriceElement from '../../../utilities/special-price/PriceElement';
import {
  addShippingInCart, removeFullScreenLoader,
  showFullScreenLoader
} from '../../../utilities/checkout_util';
import {
  gerAreaLabelById
} from '../../../utilities/address_util';

export default class ShippingMethod extends React.Component {

  constructor(props) {
    super(props);
    this.state = {
      'selectedOption': this.props.selected
    };
  }

  /**
   * Handles shipping method change.
   */
  changeShippingMethod = (method) => {
    const cart = this.props.cart.cart;
    let selectCarrierInfo = method.carrier_code + '_' + method.method_code

    // If mathod is already selected in cart.
    if (cart.carrier_info === selectCarrierInfo) {
      return;
    }

    // Show the loader.
    showFullScreenLoader();

    var event = new CustomEvent('changeShippingMethod', {
      bubbles: true,
      detail: {
        data: method
      }
    });
    document.dispatchEvent(event);

    let data = {};
    data['carrier_info'] = {
      'carrier': method.carrier_code,
      'method': method.method_code
    }

    // For anonymous users.
    data.static = {
      firstname: cart.shipping_address.firstname,
      lastname: cart.shipping_address.lastname,
      email: cart.shipping_address.email,
      city: gerAreaLabelById(false, cart.shipping_address.area),
      telephone: cart.shipping_address.telephone,
      country_id: window.drupalSettings.country_code,
    };

    // Getting dynamic fields data.
    Object.entries(window.drupalSettings.address_fields).forEach(([key, field]) => {
      data[field.key] = cart.shipping_address[field.key];
    });

    if (window.drupalSettings.user.uid > 0) {
      data['static']['customer_address_id'] = cart.shipping_address.customer_address_id;
      data['static']['customer_id'] = cart.shipping_address.customer_id;
    }

    // Update shipping address.
    let cart_data = addShippingInCart('update shipping', data);
    if (cart_data instanceof Promise) {
      cart_data.then((cart_result) => {
        // If no error.
        if (cart_result.error === undefined) {
          let cart_info = {
            cart: cart_result
          }

          // Update state and radio button.
          this.setState({
            selectedOption: method.method_code
          });
          document.getElementById('shipping-method-' + method.method_code).checked = true;
          // Refresh cart.
          this.props.refreshCart(cart_info);
        }
        else {
          // In case of error, prepare error info
          // and call refresh cart so that message is shown.
          let error_info = {
            'error_code': cart_result.error_code,
            'error_message': cart_result.error_message
          }
          this.props.refreshCart(error_info);
        }

        // Remove loader.
        removeFullScreenLoader();
      });
    }
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
