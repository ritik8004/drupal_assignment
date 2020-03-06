import React from 'react';

import Popup from 'reactjs-popup';
import BillingInfo from '../billing-info';
import AddressForm from '../address-form';
import {
  processBillingUpdateFromForm
} from '../../../utilities/checkout_address_process';

export default class CnCBillingAddress extends React.Component {

  constructor(props) {
    super(props);
    this.state = {
      open: false
    };
  }

  /**
   * For modal open.
   */
  openModal = () => {
    this.setState({
      open: true
    });
  };

  /**
   * For modal closing.
   */
  closeModal = () => {
    this.setState({
      open: false
    });
  };

  /**
   * Process address submission here.
   */
  processAddress = (e) => {
    const shipping = this.props.cart.cart.shipping_address;
    console.log(shipping);
    return processBillingUpdateFromForm(e, shipping);
  };

  componentDidMount() {
    document.addEventListener('onBillingAddressUpdate', this.processBillingUpdate, false);
  }

  /**
   * Event handler for billing update.
   */
  processBillingUpdate = (e) => {
    let data = e.detail.data();
    console.log(data);
    // Refresh cart.
    this.props.refreshCart(data);

    // Close the modal.
    this.closeModal();
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
    const shipping = cart.cart.shipping_address;
    // If billing address is not set already.
    if (billingAddress === undefined
      || billingAddress === null) {
      return (
        <React.Fragment>
          <div onClick={this.openModal}>
            {Drupal.t('please add your billing address.')}
          </div>
          <Popup
            open={this.state.open}
            onClose={this.closeModal}
            closeOnDocumentClick={false}
          >
          <AddressForm
            closeModal={this.closeModal}
            processAddress={this.processAddress}
            showEmail={false}
            default_val={null}
          />
        </Popup>
        </React.Fragment>
      );
    }

    return (
      <BillingInfo refreshCart={this.props.refreshCart} shipping={shipping} billing={billingAddress}/>
    );
  }

}
