import React from 'react';

import BillingPopUp from '../billing-popup';
import BillingInfo from '../billing-info';
import SectionTitle from '../../../utilities/section-title';

export default class CnCBillingAddress extends React.Component {
  isComponentMounted = false;

  constructor(props) {
    super(props);
    this.state = {
      open: false,
    };
  }

  componentDidMount() {
    this.isComponentMounted = true;
    document.addEventListener('onBillingAddressUpdate', this.processBillingUpdate, false);
  }

  componentWillUnmount() {
    this.isComponentMounted = false;
    document.removeEventListener('onBillingAddressUpdate', this.processBillingUpdate, false);
  }

  showPopup = () => {
    this.setState({
      open: true,
    });
  };

  closePopup = () => {
    this.setState({
      open: false,
    });
  };

  /**
   * Event handler for billing update.
   */
  processBillingUpdate = (e) => {
    const data = e.detail.data();
    const { refreshCart } = this.props;
    if (this.isComponentMounted) {
      // Close modal.
      this.closePopup();
      // Refresh cart.
      refreshCart(data);
    }
  };

  render() {
    const { billingAddress, shippingAddress, carrierInfo } = this.props;
    const { open } = this.state;

    // If carrier info not set, means shipping info not set.
    // So we don't need to show bulling.
    if (carrierInfo === undefined || carrierInfo === null) {
      return (null);
    }

    // If billing address city value is 'NONE',
    // means its default billing address (same as shipping)
    // and not added by the user.
    let billingAddressAddedByUser = true;
    if (billingAddress.city === 'NONE') {
      billingAddressAddedByUser = false;
    }

    // If user has not added billing address.
    if (!billingAddressAddedByUser) {
      return (
        <div className="spc-section-billing-address cnc-flow">
          <SectionTitle>{Drupal.t('Billing address')}</SectionTitle>
          <div className="spc-billing-address-wrapper">
            <div className="spc-billing-top-panel spc-billing-cc-panel" onClick={(e) => this.showPopup(e)}>
              {Drupal.t('please add your billing address.')}
            </div>
            {open
              && (
                <BillingPopUp
                  closePopup={this.closePopup}
                  billing={null}
                  shipping={shippingAddress}
                />
              )}
          </div>
        </div>
      );
    }

    return (
      <div className="spc-section-billing-address cnc-flow">
        <SectionTitle>{Drupal.t('billing address')}</SectionTitle>
        <div className="spc-billing-address-wrapper">
          <div className="spc-billing-bottom-panel">
            <BillingInfo shipping={shippingAddress} billing={billingAddress} />
          </div>
        </div>
      </div>
    );
  }
}
