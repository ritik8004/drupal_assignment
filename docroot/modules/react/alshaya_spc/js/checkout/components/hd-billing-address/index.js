import React from 'react';

import BillingInfo from '../billing-info';
import BillingPopUp from '../billing-popup';

// Storage key for billing shipping info same or not.
const localStorageKey = 'billing_shipping_different';

export default class HDBillingAddress extends React.Component {

  _isMounted = false;

  constructor(props) {
    super(props);
    this.state = {
      open: false,
      shippingAsBilling: true
    };

  }

  showPopup = (showHide) => {
    this.setState({
      open: showHide
    });
  }

  closePopup = () => {
    if (this._isMounted) {
      this.setState({
        open: false
      });
    }
  };

  /**
   * On billing address change.
   */
  changeBillingAddress = (shippingAsBilling) => {
    this.setState({
      shippingAsBilling: shippingAsBilling
    });

    if (shippingAsBilling === true) {
      // If shipping and billing same, we remove
      // local storage.
      localStorage.removeItem(localStorageKey);
    }

    this.showPopup(!shippingAsBilling);
  };

  componentDidMount() {
    this._isMounted = true;
    document.addEventListener('onBillingAddressUpdate', this.processBillingUpdate, false);
  }

  componentWillUnmount() {
    this._isMounted = false;
  }

  /**
   * Event handler for billing update.
   */
  processBillingUpdate = (e) => {
    let data = e.detail.data();

    // If there is no error and update was fine, means user
    // has changed the billing address. We set in localstorage.
    if (data.error === undefined) {
      if (data.cart !== undefined
        && data.cart.delivery_type === 'hd') {
        localStorage.setItem(localStorageKey, true);
      }
    }

    // Refresh cart.
    this.props.refreshCart(data);
  };

  /**
   * If local storage has biliing shipping set.
   */
  isBillingSameAsShippingInStorage = () => {
    let same = localStorage.getItem(localStorageKey);
    return (same === null || same === undefined);
  };

  render() {
    const {
      billingAddress,
      shippingAddress,
      carrierInfo,
      paymentMethod
    } = this.props;
    // If carrier info not set on cart, means shipping is not
    // set. Thus billing is also not set and thus no need to
    // show biiling info.
    if (carrierInfo === undefined
      || carrierInfo === null) {
      return (null);
    }

    // No need to show the billing address change for the
    // COD payment method.
    if (paymentMethod === undefined
      || paymentMethod === 'cashondelivery') {
      return (null);
    }

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
        {this.state.open &&
          <BillingPopUp closePopup={this.closePopup} billing={billingAddress} shipping={shippingAddress}/>
        }
        {!this.isBillingSameAsShippingInStorage() &&
          <BillingInfo shipping={shippingAddress} billing={billingAddress}/>
        }
      </React.Fragment>
    );
  }

}
