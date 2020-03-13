import React from 'react';

import Popup from 'reactjs-popup';
import AddressForm from '../address-form';
import {
  addEditAddressToCustomer,
  gerAreaLabelById
} from '../../../utilities/address_util';
import {
  addShippingInCart,
  cleanMobileNumber,
  showFullScreenLoader,
  removeFullScreenLoader,
  triggerCheckoutEvent
} from '../../../utilities/checkout_util';
import {
  prepareAddressDataForShipping
} from '../../../utilities/checkout_address_process';
import EditAddressSVG from "../edit-address-svg";

export default class AddressItem extends React.Component {

  constructor(props) {
    super(props);
    this.state = {
      open: false
    };
  }

  openModal = (e) => {
    this.setState({
      open: true
    });

    e.stopPropagation();
  };

  closeModal = () => {
    this.setState({
      open: false
    });
  };

  /**
   * Prepare address data to update shipping when
   */
  prepareAddressToUpdate = (address) => {
    address.city = gerAreaLabelById(false, address['administrative_area']);
    address.mobile = cleanMobileNumber(address.mobile);
    let data = prepareAddressDataForShipping(address);
    data['static']['customer_address_id'] = address.address_mdc_id;
    data['static']['customer_id'] = address.customer_id;
    return data;
  };

  /**
   * When user changes address.
   */
  updateShippingAddress = (address) => {
    // If address we selecting is already shipping address,
    // don't do anything.
    if (this.props.isSelected) {
      return;
    }

    // Show loader.
    showFullScreenLoader();

    // Prepare address data for shipping info update.
    let data = this.prepareAddressToUpdate(address);

    // Update shipping on cart.
    let cart_info = addShippingInCart('update shipping', data);
    if (cart_info instanceof Promise) {
      cart_info.then((cart_result) => {
        // Remove loader.
        removeFullScreenLoader();

        // Prepare cart data.
        let cart_data = {
          'cart': cart_result
        }
        // If there is any error.
        if (cart_result.error !== undefined) {
          cart_data = {
            'error_message': cart_result.error_message
          }
        }

        // Trigger event to close all popups.
        triggerCheckoutEvent('refreshCartOnAddress', cart_data);
      });
    }
  };

  componentDidMount() {
    // Close the modal
    document.addEventListener('closeAddressListPopup', this.closeModal, false);
  }

  /**
   * Process the address form data on sumbit.
   */
  processAddress = (e) => {
    // Show loader.
    showFullScreenLoader();
    addEditAddressToCustomer(e);
  };

  render() {
    const { address } = this.props;
    const mob_default_val = cleanMobileNumber(address.mobile);
    let addressData = [];
    let editAddressData = {};
    Object.entries(drupalSettings.address_fields).forEach(([key, val]) => {
      if (address[key] !== undefined) {
        let fillVal = address[key];
        // Handling for area field.
        if (key === 'administrative_area') {
          fillVal = address['area_label'];
        }
        // Handling for parent area.
        else if (key === 'area_parent') {
          fillVal = address['area_parent_label'];
        }

        addressData.push(fillVal);
        editAddressData[val['key']] = address[key];
      }
    })

    editAddressData['static'] = {};
    editAddressData['static']['fullname'] = address['given_name'] + ' ' + address['family_name'];
    editAddressData['static']['telephone'] = mob_default_val;
    editAddressData['static']['address_id'] = address['address_id'];

    let selectedClass = this.props.isSelected === true ? ' selected' : '';
    let buttonText = this.props.isSelected === true ? Drupal.t('selected') : Drupal.t('select');

    return (
      <div className={'spc-address-tile' + selectedClass}>
      <div className='spc-address-metadata'>
        <div className='spc-address-name'>{address.given_name} {address.family_name}</div>
        <div className='spc-address-fields'>{addressData.join(', ')}</div>
        <div className='spc-address-mobile'>+{drupalSettings.country_mobile_code} {mob_default_val}</div>
      </div>
      <div className='spc-address-tile-actions'>
        <div className='spc-address-btns'>
          <button className='spc-address-select-address' onClick={() => this.updateShippingAddress(address)}>{buttonText}</button>
          <div title={Drupal.t('Edit Address')} className='spc-address-tile-edit' onClick={(e) => this.openModal(e)}>
            <EditAddressSVG/>
            <Popup open={this.state.open} onClose={this.closeModal} closeOnDocumentClick={false}>
              <React.Fragment>
                <AddressForm closeModal={this.closeModal} showEmail={false} show_prefered={true} default_val={editAddressData} processAddress={this.processAddress} />
              </React.Fragment>
            </Popup>
          </div>
        </div>
      </div>
      </div>
    );
  }

}
