import React from 'react';

import BillingInfo from '../billing-info';
import BillingPopUp from '../billing-popup';

export default class HDBillingAddress extends React.Component {

  constructor(props) {
    super(props);
    this.state = {
      showPopup: false,
      shippingAsBilling: true
    };

  }

  showPopup = (showHide) => {
    this.setState({
      showPopup: showHide
    });
  }

  /**
   * On billing address change.
   */
  changeBillingAddress = (shippingAsBilling) => {
    this.setState({
      shippingAsBilling: shippingAsBilling
    });

    this.showPopup(!shippingAsBilling);
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
    // If carrier info not set on cart, means shipping is not
    // set. Thus billing is also not set and thus no need to
    // show biiling info.
    if (cart.cart.carrier_info === undefined
      || cart.cart.carrier_info === null) {
      return (null);
    }

    let billingAddress = cart.cart.billing_address;
    let shippingAddress = cart.cart.shipping_address;
    const isShippingBillingSame = this.state.shippingAsBilling;

    return (
      <React.Fragment>
        <div onClick={() => this.changeBillingAddress(true)}>
        <div>{Drupal.t('billing address same as delivery address?')}</div>
          <input id='billing-address-yes' defaultChecked={isShippingBillingSame} value={true} name='billing-address' type='radio'/>
          <label className='radio-sim radio-label'>{Drupal.t('yes')}</label>
        </div>
        <div onClick={() => this.changeBillingAddress(false)}>
          <input id='billing-address-no' defaultChecked={!isShippingBillingSame} value={false} name='billing-address' type='radio'/>
          <label className='radio-sim radio-label'>{Drupal.t('no')}</label>
        </div>
        {this.state.showPopup &&
          <BillingPopUp billing={billingAddress} shipping={shippingAddress}/>
        }
        {!isShippingBillingSame &&
          <BillingInfo shipping={shippingAddress} billing={billingAddress}/>
        }
      </React.Fragment>
    );
  }

}
