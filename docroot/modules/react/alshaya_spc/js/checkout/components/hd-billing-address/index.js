import React from 'react';

import BillingInfo from '../billing-info';
import SectionTitle from '../../../utilities/section-title';
import {
  isBillingSameAsShippingInStorage,
  removeBillingFlagFromStorage,
} from '../../../utilities/checkout_util';

// Storage key for billing shipping info same or not.
const localStorageKey = 'billing_shipping_same';

export default class HDBillingAddress extends React.Component {
  isComponentMounted = false;

  constructor(props) {
    super(props);
    this.state = {
      shippingAsBilling: isBillingSameAsShippingInStorage(),
    };

    // Check and remove flag on load.
    removeBillingFlagFromStorage(props.cart);
  }

  componentDidMount() {
    this.isComponentMounted = true;
    document.addEventListener('onBillingAddressUpdate', this.processBillingUpdate, false);
    document.addEventListener('onShippingAddressUpdate', this.processShippingUpdate, false);
  }

  componentWillUnmount() {
    this.isComponentMounted = false;
    document.removeEventListener('onBillingAddressUpdate', this.processBillingUpdate, false);
    document.removeEventListener('onShippingAddressUpdate', this.processShippingUpdate, false);
  }

  /**
   * Event handler for shipping update.
   */
  processShippingUpdate = (e) => {
    const data = e.detail;
    // If there is no error and update was fine, means user
    // has added billing address. We set in localstorage.
    if (data.error === undefined) {
      if (data.cart_id !== undefined
        && data.delivery_type === 'hd'
        && isBillingSameAsShippingInStorage()) {
        localStorage.setItem(localStorageKey, true);
        this.setState({
          shippingAsBilling: true,
        });
      }
    }
  };

  /**
   * Event handler for billing update.
   */
  processBillingUpdate = (e) => {
    const data = e.detail;
    // If there is no error and update was fine, means user
    // has changed the billing address. We set in localstorage.
    if (data.error === undefined) {
      if (data.cart !== undefined) {
        localStorage.setItem(localStorageKey, false);
        this.setState({
          shippingAsBilling: false,
        });
      }
    }
  };

  /**
   * Message to show when billing is
   * same as shipping.
   */
  sameBillingAsShippingMessage = () => Drupal.t('We have set your billing address same as delivery address. You can select a different one by clicking the change button above.');

  render() {
    const { cart, refreshCart } = this.props;
    const { shippingAsBilling } = this.state;
    // If carrier info not set on cart, means shipping is not
    // set. Thus billing is also not set and thus no need to
    // show billing info.
    if (cart.cart.carrier_info === undefined
      || cart.cart.carrier_info === null
      || cart.cart.billing_address === null
      || cart.cart.billing_address.city === 'NONE') {
      return (null);
    }

    // No need to show the billing address change for the
    // COD payment method.
    if (cart.cart.cart_payment_method === undefined
      || cart.cart.cart_payment_method === 'cashondelivery') {
      return (null);
    }

    return (
      <div className="spc-section-billing-address">
        <SectionTitle>{Drupal.t('billing address')}</SectionTitle>
        <div className="spc-billing-address-wrapper">
          <div className="spc-billing-bottom-panel">
            <BillingInfo cart={cart} refreshCart={refreshCart} />
            {shippingAsBilling
            && <div className="spc-billing-help-text">{this.sameBillingAsShippingMessage()}</div>}
          </div>
        </div>
      </div>
    );
  }
}
