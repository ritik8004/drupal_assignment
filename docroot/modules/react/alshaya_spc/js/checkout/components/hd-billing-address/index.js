import React from 'react';

import BillingInfo from '../billing-info';
import BillingPopUp from '../billing-popup';
import SectionTitle from '../../../utilities/section-title';

// Storage key for billing shipping info same or not.
const localStorageKey = 'billing_shipping_different';

export default class HDBillingAddress extends React.Component {
  _isMounted = false;

  constructor(props) {
    super(props);
    this.state = {
      open: false,
      shippingAsBilling: this.isBillingSameAsShippingInStorage(),
    };
  }

  componentDidMount() {
    this._isMounted = true;
    document.addEventListener('onBillingAddressUpdate', this.processBillingUpdate, false);
    document.addEventListener('onShippingAddressUpdate', this.processShippingUpdate, false);
  }

  componentWillUnmount() {
    this._isMounted = false;
  }

  showPopup = (showHide) => {
    this.setState({
      open: showHide,
    });
  }

  closePopup = () => {
    if (this._isMounted) {
      this.setState({
        open: false,
      });
      this.setStateAndChecked(true);
    }
  };

  setStateAndChecked = (shippingAsBilling) => {
    this.setState({
      shippingAsBilling,
    });

    const yesNO = shippingAsBilling ? 'yes' : 'no';
    document.getElementById(`billing-address-${yesNO}`).checked = true;
  };

  /**
   * On billing address change.
   */
  changeBillingAddress = (shippingAsBilling) => {
    const { shippingAsBilling: shippingBilling } = this.state;
    // Do nothing if user tries to select already selected option.
    if (shippingBilling === shippingAsBilling) {
      return;
    }

    this.setStateAndChecked(shippingAsBilling);

    if (shippingAsBilling === true) {
      // If shipping and billing same, we remove
      // local storage.
      localStorage.removeItem(localStorageKey);
    }

    this.showPopup(!shippingAsBilling);
  };

  /**
   * Handle shipping update event.
   */
  processShippingUpdate = () => {
    // Remove local storage so that user fills billing again.
    localStorage.removeItem(localStorageKey);
    this.setStateAndChecked(true);
  }

  /**
   * Event handler for billing update.
   */
  processBillingUpdate = (e) => {
    const data = e.detail.data();
    const { refreshCart } = this.props;
    // If there is no error and update was fine, means user
    // has changed the billing address. We set in localstorage.
    if (data.error === undefined) {
      if (data.cart !== undefined
        && data.cart.delivery_type === 'hd') {
        localStorage.setItem(localStorageKey, false);
      }
    }

    // CLose modal.
    if (this._isMounted) {
      this.setState({
        open: false,
      });
    }

    // Refresh cart.
    refreshCart(data);
  };

  /**
   * If local storage has biliing shipping set.
   */
  isBillingSameAsShippingInStorage = () => {
    const same = localStorage.getItem(localStorageKey);
    return (same === null || same === undefined);
  };

  render() {
    const {
      billingAddress,
      shippingAddress,
      carrierInfo,
      paymentMethod,
    } = this.props;
    const { open } = this.state;
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

    const isShippingBillingSame = this.isBillingSameAsShippingInStorage();

    return (
      <div className="spc-section-billing-address">
        <SectionTitle>{Drupal.t('Billing address')}</SectionTitle>
        <div className="spc-billing-address-wrapper">
          <div className="spc-billing-top-panel">
            <div className="spc-billing-address-title">{Drupal.t('billing address same as delivery address?')}</div>
            <div className="spc-billing-address-btns">
              <div className="spc-billing-radio" onClick={() => this.changeBillingAddress(true)}>
                <input id="billing-address-yes" defaultChecked={isShippingBillingSame} value name="billing-address" type="radio" />
                <label className="radio-sim radio-label">{Drupal.t('yes')}</label>
              </div>
              <div className="spc-billing-radio" onClick={() => this.changeBillingAddress(false)}>
                <input id="billing-address-no" defaultChecked={!isShippingBillingSame} value={false} name="billing-address" type="radio" />
                <label className="radio-sim radio-label">{Drupal.t('no')}</label>
              </div>
            </div>
          </div>
          {open
            && (
            <BillingPopUp
              closePopup={this.closePopup}
              billing={billingAddress}
              shipping={shippingAddress}
            />
            )}
          {!this.isBillingSameAsShippingInStorage()
            && (
            <div className="spc-billing-bottom-panel">
              <BillingInfo shipping={shippingAddress} billing={billingAddress} />
            </div>
            )}
        </div>
      </div>
    );
  }
}
