import React from 'react';

import BillingInfo from '../billing-info';

export default class HDBillingAddress extends React.Component {

  constructor(props) {
    super(props);
    this.state = {
      shippingAsBilling: this.isShippingBillingAddressSame()
    };

  }

  /**
   * Checks if shipping and billing address same.
   * This checks in local storage and if not found
   * treated as true.
   */
  isShippingBillingAddressSame = () => {
    const shippingBillingSame = localStorage.getItem('shipping_as_billing');
    return (shippingBillingSame === null ||
      shippingBillingSame === undefined);
  };

  /**
   * On billing address change.
   */
  changeBillingAddress = (shippingAsBilling) => {
    if (shippingAsBilling === true) {
      // If shipping and billing both same,
      // remove local storage.
      localStorage.removeItem('shipping_as_billing');
    }
    else {
      // Set in the user local storage if shipping address
      // is not used for billing.
      localStorage.setItem('shipping_as_billing', shippingAsBilling);
    }

    this.setState({
      shippingAsBilling: shippingAsBilling
    });
  };

  render() {
    const { cart } = this.props;
    // If carrier info not set on cart, means shipping is not
    // set. Thus billing is also not set and thus no need to
    // show biiling info.
    if (cart.cart.carrier_info === undefined
      || cart.cart.carrier_info === null) {
      return (null);
    }

    const isShippingBillingSame = this.state.shippingAsBilling;
    let billingAddress = isShippingBillingSame
      ? null
      : cart.cart.billing_address;

    const shipping = cart.cart.shipping_address;

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
        <BillingInfo refreshCart={this.props.refreshCart}  shipping={shipping} billing={billingAddress}/>
      </React.Fragment>
    );
  }

}
