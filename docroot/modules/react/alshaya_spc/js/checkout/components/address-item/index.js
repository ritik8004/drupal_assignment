import React from 'react';

import Popup from 'reactjs-popup';
import AddressForm from '../address-form';
import {
  addEditAddressToCustomer,
  gerAreaLabelById,
} from '../../../utilities/address_util';
import {
  addShippingInCart,
  addBillingInCart,
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
  getInfoFromStorage,
} from '../../../utilities/storage';

export default class AddressItem extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      open: false,
    };
  }

  componentDidMount() {
    // Close the modal.
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
    addressUpdate.mobile = `+${drupalSettings.country_mobile_code}${cleanMobileNumber(address.mobile)}`;
    const data = prepareAddressDataForShipping(addressUpdate);
    data.static.customer_address_id = address.address_mdc_id;
    data.static.customer_id = address.customer_id;
    return data;
  };

  /**
   * When user changes address.
   */
  updateAddress = (address) => {
    const { isSelected, type } = this.props;
    // If address we selecting is already used address,
    // don't do anything.
    if (isSelected) {
      return;
    }

    // Show loader.
    showFullScreenLoader();

    // Prepare address data for address info update.
    const data = this.prepareAddressToUpdate(address);

    // Update address on cart.
    const cartInfo = type === 'billing'
      ? addBillingInCart('update billing', data)
      : addShippingInCart('update shipping', data);

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
        } else {
          cartData = getInfoFromStorage();
          cartData.cart = cartResult;
        }

        // Trigger event to close shipping popups.
        triggerCheckoutEvent('refreshCartOnAddress', cartData);
        if (type === 'billing') {
          // Trigger event to close billing popups.
          triggerCheckoutEvent('onBillingAddressUpdate', cartData);
        }
      });
    }
  };

  /**
   * Process the address form data on sumbit.
   */
  processAddress = (e) => {
    const { type, processAddress } = this.props;
    // Show loader.
    showFullScreenLoader();
    // If processing method is passed, we use that.
    if (type === 'billing') {
      processAddress(e);
    } else {
      addEditAddressToCustomer(e);
    }
  };

  render() {
    const {
      address,
      isSelected,
      headingText,
      showEditButton,
    } = this.props;
    const mobDefaultVal = cleanMobileNumber(address.mobile);
    const addressData = [];
    const editAddressData = {};
    const { open } = this.state;
    Object.entries(drupalSettings.address_fields).forEach(([key, val]) => {
      if (address[key] !== undefined) {
        let fillVal = address[key];

        if (key === 'administrative_area') {
          // Handling for area field.
          fillVal = address.area_label;
        } else if (key === 'area_parent') {
          // Handling for parent area.
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
            <button type="button" disabled={isSelected} className="spc-address-select-address" onClick={() => this.updateAddress(address)}>{buttonText}</button>
            {(showEditButton === undefined || showEditButton === true) &&
              <div title={Drupal.t('Edit Address')} className="spc-address-tile-edit" onClick={(e) => this.openModal(e)}>
                <EditAddressSVG />
                <Popup open={open} onClose={this.closeModal} closeOnDocumentClick={false}>
                  <>
                    <AddressForm
                      closeModal={this.closeModal}
                      showEmail={false}
                      headingText={headingText}
                      show_prefered
                      default_val={editAddressData}
                      processAddress={this.processAddress}
                    />
                  </>
                </Popup>
              </div>}
          </div>
        </div>
      </div>
    );
  }
}
