import React from 'react';

import Popup from 'reactjs-popup';
import AddressForm from '../address-form';
import {
  processBillingUpdateFromForm,
  formatAddressDataForEditForm,
} from '../../../utilities/address_util';

export default class BillingPopUp extends React.Component {
  isComponentMounted = false;

  constructor(props) {
    super(props);
    this.state = {
      open: true,
    };
  }

  componentDidMount() {
    this.isComponentMounted = true;
    document.addEventListener('onBillingAddressUpdate', this.processBillingUpdate, false);
  }

  componentWillUnmount() {
    this.isComponentMounted = false;
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
    const { closePopup } = this.props;
    if (this.isComponentMounted) {
      this.setState({
        open: false,
      });

      closePopup();
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

  /**
   * Event handler for billing update.
   */
  processBillingUpdate = () => {
    if (this.isComponentMounted) {
      this.setState({
        open: false,
      });
    }
  }

  render() {
    const { open } = this.state;
    const { billing } = this.props;
    return (
      <>
        <Popup
          open={open}
          onClose={this.closeModal}
          closeOnDocumentClick={false}
        >
          <AddressForm
            closeModal={this.closeModal}
            processAddress={this.processAddress}
            showEmail={false}
            headingText={Drupal.t('billing information')}
            default_val={this.formatAddressData(billing)}
          />
        </Popup>
      </>
    );
  }
}
