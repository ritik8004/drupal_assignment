import React from 'react';

import Popup from 'reactjs-popup';
import AddressForm from '../address-form';
import {
  addEditAddressToCustomer,
  gerAreaLabelById,
} from '../../../utilities/address_util';
import {
  addShippingInCart,
  cleanMobileNumber,
  showFullScreenLoader,
  removeFullScreenLoader,
  triggerCheckoutEvent,
} from '../../../utilities/checkout_util';
import {
  prepareAddressDataForShipping,
} from '../../../utilities/checkout_address_process';
import EditAddressSVG from '../edit-address-svg';
import {
  getInfoFromStorage
} from '../../../utilities/storage';

export default class AddressItem extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      open: false,
    };
  }

  componentDidMount() {
    // Close the modal
    document.addEventListener('closeAddressListPopup', this.closeModal, false);
  }

  openModal = (e) => {
    this.setState({
      open: true,
    });

    e.stopPropagation();
  };

  closeModal = () => {
    this.setState({
      open: false,
    });
  };

  /**
   * Prepare address data to update shipping when
   */
  prepareAddressToUpdate = (address) => {
    const addressUpdate = address;
    addressUpdate.city = gerAreaLabelById(false, address.administrative_area);
    addressUpdate.mobile = cleanMobileNumber(address.mobile);
    const data = prepareAddressDataForShipping(addressUpdate);
    data.static.customer_address_id = address.address_mdc_id;
    data.static.customer_id = address.customer_id;
    return data;
  };

  /**
   * When user changes address.
   */
  updateShippingAddress = (address) => {
    const { isSelected } = this.props;
    // If address we selecting is already shipping address,
    // don't do anything.
    if (isSelected) {
      return;
    }

    // Show loader.
    showFullScreenLoader();

    // Prepare address data for shipping info update.
    const data = this.prepareAddressToUpdate(address);

    // Update shipping on cart.
    const cartInfo = addShippingInCart('update shipping', data);
    if (cartInfo instanceof Promise) {
      cartInfo.then((cartResult) => {
        // Remove loader.
        removeFullScreenLoader();

        // Prepare cart data.
        let cartData = {};
        // If there is any error.
        if (cartResult.error !== undefined) {
          cartData = {
            error_message: cartResult.error_message,
          };
        }
        else {
          cartData = getInfoFromStorage();
          cartData.cart = cartResult;
        }

        // Trigger event to close all popups.
        triggerCheckoutEvent('refreshCartOnAddress', cartData);
      });
    }
  };

  /**
   * Process the address form data on sumbit.
   */
  processAddress = (e) => {
    // Show loader.
    showFullScreenLoader();
    addEditAddressToCustomer(e);
  };

  render() {
    const { address, isSelected } = this.props;
    const mobDefaultVal = cleanMobileNumber(address.mobile);
    const addressData = [];
    const editAddressData = {};
    const { open } = this.state;
    Object.entries(drupalSettings.address_fields).forEach(([key, val]) => {
      if (address[key] !== undefined) {
        let fillVal = address[key];
        // Handling for area field.
        if (key === 'administrative_area') {
          fillVal = address.area_label;
        }
        // Handling for parent area.
        else if (key === 'area_parent') {
          fillVal = address.area_parent_label;
        }

        addressData.push(fillVal);
        editAddressData[val.key] = address[key];
      }
    });

    editAddressData.static = {};
    editAddressData.static.fullname = `${address.given_name} ${address.family_name}`;
    editAddressData.static.telephone = mobDefaultVal;
    editAddressData.static.address_id = address.address_id;

    const selectedClass = isSelected === true ? ' selected' : '';
    const buttonText = isSelected === true ? Drupal.t('selected') : Drupal.t('select');

    return (
      <div className={`spc-address-tile${selectedClass}`}>
        <div className="spc-address-metadata">
          <div className="spc-address-name">
            {address.given_name}
            {' '}
            {address.family_name}
          </div>
          <div className="spc-address-fields">{addressData.join(', ')}</div>
          <div className="spc-address-mobile">
            +
            {drupalSettings.country_mobile_code}
            {' '}
            {mobDefaultVal}
          </div>
        </div>
        <div className="spc-address-tile-actions">
          <div className="spc-address-btns">
            <button disabled={isSelected} className="spc-address-select-address" onClick={() => this.updateShippingAddress(address)}>{buttonText}</button>
            <div title={Drupal.t('Edit Address')} className="spc-address-tile-edit" onClick={(e) => this.openModal(e)}>
              <EditAddressSVG />
              <Popup open={open} onClose={this.closeModal} closeOnDocumentClick={false}>
                <>
                  <AddressForm
                    closeModal={this.closeModal}
                    showEmail={false}
                    show_prefered
                    default_val={editAddressData}
                    processAddress={this.processAddress}
                  />
                </>
              </Popup>
            </div>
          </div>
        </div>
      </div>
    );
  }
}
