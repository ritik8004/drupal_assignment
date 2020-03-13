import React from 'react';

import Popup from 'reactjs-popup';
import AddressForm from '../address-form';
import {
  processBillingUpdateFromForm,
  formatAddressDataForEditForm,
} from '../../../utilities/checkout_address_process';

export default class BillingPopUp extends React.Component {
  _isMounted = false;

  constructor(props) {
    super(props);
    this.state = {
      open: true,
    };
  }

  /**
   * For modal open.
   */
  openModal = () => {
    this.setState({
      open: true,
    });
  };

  /**
   * For modal closing.
   */
  closeModal = () => {
    if (this._isMounted) {
      this.setState({
        open: false,
      });

      this.props.closePopup();
    }
  };

  /**
   * Process the address.
   */
  processAddress = (e) => {
    const { shipping } = this.props;
    return processBillingUpdateFromForm(e, shipping);
  }

  /**
   * Format address for edit address.
   */
  formatAddressData = (address) => (address === null
    ? address
    : formatAddressDataForEditForm(address))

  componentDidMount() {
    this._isMounted = true;
    document.addEventListener('onBillingAddressUpdate', this.processBillingUpdate, false);
  }

  /**
   * Event handler for billing update.
   */
  processBillingUpdate = (e) => {
    if (this._isMounted) {
      this.setState({
        open: false,
      });
    }
  }

  componentWillUnmount() {
    this._isMounted = false;
  }

  render() {
    return (
      <>
        <Popup
          open={this.state.open}
          onClose={this.closeModal}
          closeOnDocumentClick={false}
        >
          <AddressForm
            closeModal={this.closeModal}
            processAddress={this.processAddress}
            showEmail={false}
            headingText={Drupal.t('billing information')}
            default_val={this.formatAddressData(this.props.billing)}
          />
        </Popup>
      </>
    );
  }
}
