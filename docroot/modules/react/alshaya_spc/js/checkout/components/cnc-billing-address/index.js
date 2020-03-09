import React from 'react';

import BillingPopUp from '../billing-popup';
import BillingInfo from '../billing-info';
import SectionTitle from "../../../utilities/section-title";

export default class CnCBillingAddress extends React.Component {

  _isMounted = false;

  constructor(props) {
    super(props);
    this.state = {
      open: false
    };
  }

  showPopup = () => {
    this.setState({
      open: true
    });
  };

  closePopup = () => {
    if (this._isMounted) {
      this.setState({
        open: false
      });
    }
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
    // Refresh cart.
    this.props.refreshCart(data);
  };

  render() {
    const { billingAddress, shippingAddress, carrierInfo } = this.props;

    // If carrier info not set, means shipping info not set.
    // So we don't need to show bulling.
    if (carrierInfo === undefined || carrierInfo === null) {
        return (null);
    }

    // If billing address is not set already.
    if (billingAddress === undefined || billingAddress === null) {
      return (
        <div className='spc-section-billing-address cnc-flow'>
          <SectionTitle>{Drupal.t('billing address')}</SectionTitle>
          <div className='spc-billing-address-wrapper'>
            <div className='spc-billing-top-panel spc-billing-cc-panel' onClick={this.showPopup()}>
              {Drupal.t('please add your billing address.')}
            </div>
            {this.state.open &&
              <BillingPopUp closePopup={this.closePopup} billing={billingAddress} shipping={shippingAddress}/>
            }
          </div>
        </div>
      );
    }

    return (
      <div className='spc-section-billing-address cnc-flow'>
        <SectionTitle>{Drupal.t('billing address')}</SectionTitle>
        <div className='spc-billing-address-wrapper'>
          <div className='spc-billing-bottom-panel'>
            <BillingInfo shipping={shippingAddress} billing={billingAddress}/>
          </div>
        </div>
      </div>
    );
  }

}
