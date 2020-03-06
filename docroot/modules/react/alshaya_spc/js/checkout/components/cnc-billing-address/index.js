import React from 'react';

import BillingPopUp from '../billing-popup';
import BillingInfo from '../billing-info';

export default class CnCBillingAddress extends React.Component {

  constructor(props) {
    super(props);
    this.state = {
      showPopup: false
    };
  }

  showPopup = () => {
    this.setState({
      showPopup: true
    });
  };

  componentDidMount() {
    document.addEventListener('onBillingAddressUpdate', this.processBillingUpdate, false);
  }

  /**
   * Event handler for billing update.
   */
  processBillingUpdate = (e) => {
    let data = e.detail.data();
    // Refresh cart.
    this.props.refreshCart(data);
  }

  render() {
    const { cart } = this.props;

    // If carrier info not set, means shipping info not set.
    // So we don't need to show bulling.
    if (cart.cart.carrier_info === undefined
      || cart.cart.carrier_info === null) {
        return (null);
    }

    const billingAddress = cart.cart.billing_address;
    const shippingAddress = cart.cart.shipping_address;
    // If billing address is not set already.
    if (billingAddress === undefined
      || billingAddress === null) {
      return (
        <React.Fragment>
          <div onClick={this.showPopup()}>
            {Drupal.t('please add your billing address.')}
          </div>
          {this.state.showPopup &&
          <BillingPopUp billing={billingAddress} shipping={shippingAddress}/>
        }
        </React.Fragment>
      );
    }

    return (
      <BillingInfo shipping={shipping} billing={billingAddress}/>
    );
  }

}
