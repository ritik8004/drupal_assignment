import React from 'react';

import Popup from 'reactjs-popup';
import AddressForm from '../address-form';
import {
  processBillingUpdateFromForm,
  formatAddressDataForEditForm
} from '../../../utilities/checkout_address_process';
import {
  gerAreaLabelById
} from '../../../utilities/address_util';

export default class BillingInfo extends React.Component {

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
   * Format address for edit address.
   */
  formatAddressData = (address) => {
    return formatAddressDataForEditForm(address);
  }

  /**
   * Process address submission here.
   */
  processAddress = (e) => {
    const shipping = this.props.shipping;
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
    // Refresh cart.
    this.props.refreshCart(data);

    // Close the modal.
    this.closeModal();
  }

  render() {
    const { billing } = this.props;
    if (billing === undefined || billing == null) {
      return (null);
    }

    let addressData = [];
    Object.entries(drupalSettings.address_fields).forEach(([key, val]) => {
      if (billing[val.key] !== undefined) {
        let fillVal = billing[val.key];
        // Handling for area field.
        if (key === 'administrative_area') {
          fillVal = gerAreaLabelById(false, fillVal);
        }
        // Handling for parent area.
        else if (key === 'area_parent') {
          fillVal = gerAreaLabelById(true, fillVal);
        }
        addressData.push(fillVal);
      }
    })

    return (
      <React.Fragment>
        <div>
          <div className='spc-delivery-customer-info'>
            <div className='delivery-name'>
              {billing.firstname} {billing.lastname}
            </div>
            <div className='delivery-address'>
              {addressData.join(', ')}
            </div>
            <div className='spc-address-form-edit-link' onClick={this.openModal}>
              {Drupal.t('Change')}
            </div>
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
            default_val={this.formatAddressData(billing)}
          />
        </Popup>
        </div>
      </React.Fragment>
    );

  }

}
