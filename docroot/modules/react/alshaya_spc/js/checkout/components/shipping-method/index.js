import React from 'react';

import PriceElement from '../../../utilities/special-price/PriceElement';
import {
  addShippingInCart, removeFullScreenLoader,
  showFullScreenLoader,
} from '../../../utilities/checkout_util';
import {
  prepareAddressDataForShipping,
} from '../../../utilities/checkout_address_process';

export default class ShippingMethod extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      selectedOption: this.props.selected,
    };
  }

  /**
   * Handles shipping method change.
   */
  changeShippingMethod = (method) => {
    const { cart } = this.props.cart;
    const selectCarrierInfo = `${method.carrier_code}_${method.method_code}`;

    // If mathod is already selected in cart.
    if (cart.carrier_info === selectCarrierInfo) {
      return;
    }

    // Show the loader.
    showFullScreenLoader();

    const event = new CustomEvent('changeShippingMethod', {
      bubbles: true,
      detail: {
        data: method,
      },
    });
    document.dispatchEvent(event);

    // Prepare shipping data.
    const tempShippingData = cart.shipping_address;
    Object.entries(drupalSettings.address_fields).forEach(([key, field]) => {
      tempShippingData[key] = cart.shipping_address[field.key];
    });
    tempShippingData.mobile = cart.shipping_address.telephone;

    // Get prepared address data for shipping address update.
    const data = prepareAddressDataForShipping(tempShippingData);
    data.carrier_info = {
      carrier: method.carrier_code,
      method: method.method_code,
    };

    // Extra info for logged in user.
    if (drupalSettings.user.uid > 0) {
      data.static.customer_address_id = cart.shipping_address.customer_address_id;
      data.static.customer_id = cart.shipping_address.customer_id;
    }

    // Update shipping address.
    const cartData = addShippingInCart('update shipping', data);
    if (cartData instanceof Promise) {
      cartData.then((cartResult) => {
        let cartInfo = {
          cart: cartResult,
        };
        // If no error.
        if (cartResult.error === undefined) {
          // Update state and radio button.
          this.setState({
            selectedOption: method.method_code,
          });
          document.getElementById(`shipping-method-${method.method_code}`).checked = true;
        } else {
          // In case of error, prepare error info
          // and call refresh cart so that message is shown.
          cartInfo = {
            error_message: cartResult.error_message,
          };
        }

        // Remove loader.
        removeFullScreenLoader();

        // Refresh cart.
        this.props.refreshCart(cartInfo);
      });
    }
  };

  render() {
    const { method } = this.props;
    let price = Drupal.t('FREE');
    if (method.amount > 0) {
      price = <PriceElement amount={method.amount} />;
    }
    return (
      <div className="shipping-method" onClick={() => this.changeShippingMethod(method)}>
        <input
          id={`shipping-method-${method.method_code}`}
          className={method.method_code}
          type="radio"
          defaultChecked={this.state.selectedOption === method.method_code}
          value={method.method_code}
          name="shipping-method"
        />

        <label className="radio-sim radio-label">
          <span className="carrier-title">{this.props.method.carrier_title}</span>
          <span className="method-title">{this.props.method.method_title}</span>
          <span className="spc-price">{price}</span>
        </label>
      </div>
    );
  }
}
