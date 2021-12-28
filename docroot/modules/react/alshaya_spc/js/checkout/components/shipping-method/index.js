import React from 'react';

import PriceElement from '../../../utilities/special-price/PriceElement';
import {
  addShippingInCart, removeFullScreenLoader,
  showFullScreenLoader,
} from '../../../utilities/checkout_util';
import {
  prepareAddressDataForShipping,
} from '../../../utilities/address_util';
import ConditionalView from '../../../common/components/conditional-view';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';

export default class ShippingMethod extends React.Component {
  constructor(props) {
    super(props);
    const { selected } = this.props;
    this.state = {
      selectedOption: selected,
    };
  }

  componentDidMount = () => {
    document.addEventListener('onShippingAddressUpdate', this.refreshShippingMethodState, false);
  }

  componentWillUnmount() {
    document.removeEventListener('onShippingAddressUpdate', this.refreshShippingMethodState, false);
  }

  /**
   * Handles shipping method update.
   */
  refreshShippingMethodState = (e) => {
    const data = e.detail;

    if (hasValue(data.shipping)
      && hasValue(data.shipping.methods)
      && hasValue(data.shipping.method)) {
      const { selectedOption } = this.state;

      Object.entries(data.shipping.methods).forEach(([, method]) => {
        if (data.shipping.method.indexOf(method.method_code) !== -1
          && selectedOption !== method.method_code) {
          this.setState({
            selectedOption: method.method_code,
          });
          document.getElementById(`shipping-method-${method.method_code}`).checked = true;
        }
      });
    }
  }

  /**
   * Handles shipping method change.
   */
  changeShippingMethod = (method) => {
    const { cart, refreshCart } = this.props;
    const selectCarrierInfo = `${method.carrier_code}_${method.method_code}`;

    // If mathod is already selected in cart.
    // After recent change in api response, need to check if method is applicable.
    if (cart.cart.shipping.method === selectCarrierInfo && method.available) {
      return;
    }

    // If method is not available.
    if (!method.available) {
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
    const tempShippingData = cart.cart.shipping.address;
    Object.entries(drupalSettings.address_fields).forEach(([key, field]) => {
      tempShippingData[key] = cart.cart.shipping.address[field.key];
    });
    tempShippingData.mobile = cart.cart.shipping.address.telephone;

    // Get prepared address data for shipping address update.
    const data = prepareAddressDataForShipping(tempShippingData);
    data.carrier_info = {
      carrier: method.carrier_code,
      method: method.method_code,
    };

    // Extra info for logged in user.
    if (drupalSettings.user.uid > 0) {
      data.static.customer_address_id = cart.cart.shipping.address.customer_address_id;
      data.static.customer_id = cart.cart.shipping.address.customer_id;
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
        refreshCart(cartInfo);
      });
    }
  };

  render() {
    const { method } = this.props;
    const { selectedOption } = this.state;
    const methodClass = method.available ? 'active' : 'disabled';
    let price = Drupal.t('FREE');
    if (method.amount > 0) {
      price = <PriceElement amount={method.amount} />;
    }
    return (
      <div className={`shipping-method ${methodClass}`} onClick={() => this.changeShippingMethod(method)}>
        <input
          id={`shipping-method-${method.method_code}`}
          className={method.method_code}
          type="radio"
          defaultChecked={selectedOption === method.method_code}
          value={method.method_code}
          name="shipping-method"
        />

        <label className="radio-sim radio-label">
          <span className="carrier-title">{method.carrier_title}</span>
          <span className="method-title">{method.method_title}</span>
          <span className="spc-price">{price}</span>
        </label>
        <ConditionalView condition={!method.available}>
          <div className="method-error-message">{method.error_message}</div>
        </ConditionalView>
      </div>
    );
  }
}
