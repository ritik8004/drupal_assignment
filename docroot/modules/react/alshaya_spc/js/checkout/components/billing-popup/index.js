import React from 'react';

import Popup from 'reactjs-popup';
import AddressForm from '../address-form';
import {
  processBillingUpdateFromForm,
  formatAddressDataForEditForm
} from '../../../utilities/checkout_address_process';

export default class BillingPopUp extends React.Component {

  constructor(props) {
    super(props);
    this.state = {
      open: true
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
   * Process the address.
   */
  processAddress = (e) => {
    const shipping = this.props.shipping;
    return processBillingUpdateFromForm(e, shipping);
  }

  /**
   * Format address for edit address.
   */
  formatAddressData = (address) => {
    return address === null
      ? address
      : formatAddressDataForEditForm(address);
  }

  /**
   * Close modal.
   */
  componentDidMount() {
    document.addEventListener('onBillingAddressUpdate', this.closeModal, false);
  }

  render() {
    return (
      <React.Fragment>
        <Popup
            open={this.state.open}
            onClose={this.closeModal}
            closeOnDocumentClick={false}
        >
          <AddressForm
            closeModal={this.closeModal}
            processAddress={this.processAddress}
            showEmail={false}
            default_val={this.formatAddressData(this.props.billing)}
          />
        </Popup>
      </React.Fragment>
    );
  }

}
