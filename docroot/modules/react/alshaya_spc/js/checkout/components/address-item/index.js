import React from 'react';

import Popup from 'reactjs-popup';
import AddressForm from '../address-form';
import {
  addEditAddressToCustomer,
  editDefaultAddressFromStorage,
  gerAreaLabelById,
  prepareAddressDataForShipping,
} from '../../../utilities/address_util';
import {
  addShippingInCart,
  addBillingInCart,
  cleanMobileNumber,
  showFullScreenLoader,
  removeFullScreenLoader,
} from '../../../utilities/checkout_util';
import EditAddressSVG from '../../../svg-component/edit-address-svg';
import dispatchCustomEvent from '../../../utilities/events';
import getStringMessage from '../../../utilities/strings';
import { getDeliveryAreaStorage, setDeliveryAreaStorage } from '../../../utilities/delivery_area_util';
import { isExpressDeliveryEnabled } from '../../../../../js/utilities/expressDeliveryHelper';

export default class AddressItem extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      open: false,
      areaUpdated: props.areaUpdated ? props.areaUpdated : false,
    };
  }

  componentDidMount() {
    // Close the modal.
    document.addEventListener('closeAddressListPopup', this.closeModal, false);
  }

  componentWillUnmount() {
    document.removeEventListener('closeAddressListPopup', this.closeModal, false);
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
      areaUpdated: false,
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
    // Save area for customer if express delivery feature enabled.
    if (isExpressDeliveryEnabled()) {
      const areaSelected = {
        label: addressUpdate.city,
        area: parseInt(address.administrative_area, 10),
        governate: parseInt(address.area_parent, 10),
      };
      setDeliveryAreaStorage(areaSelected);
    }
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
        if (!cartResult) {
          return;
        }
        // Remove loader.
        removeFullScreenLoader();

        // Prepare cart data.
        let cartData = {};
        // If there is any error.
        if (cartResult.error !== undefined) {
          cartData = {
            error_message: cartResult.error_message,
          };
        } else if (typeof cartResult.response_message !== 'undefined'
            && cartResult.response_message.status !== 'success') {
          cartData = {
            error_message: cartResult.response_message.msg,
          };
        } else {
          cartData.cart = cartResult;
        }
        // Trigger event to close shipping popups.
        dispatchCustomEvent('refreshCartOnAddress', cartData);
        if (type === 'billing') {
          // Trigger event to close billing popups.
          dispatchCustomEvent('onBillingAddressUpdate', cartData);
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
    const { areaUpdated } = this.state;
    const mobDefaultVal = cleanMobileNumber(address.mobile);
    const addressData = [];
    let editAddressData = {};
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

    // Pre-populate address form with storage area values and blank out other fields.
    // Checking if delivery area selected by user.
    if (areaUpdated && isSelected) {
      const areaSelected = getDeliveryAreaStorage();
      if (areaSelected !== null) {
        editAddressData = editDefaultAddressFromStorage(editAddressData, areaSelected);
      }
    }

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
            {address.validAddress === false
              ? <div className="address-not-valid">{getStringMessage('address_not_complete')}</div>
              : <button type="button" disabled={isSelected} className="spc-address-select-address" onClick={() => this.updateAddress(address)}>{buttonText}</button>}
            {(showEditButton === undefined || showEditButton === true)
              && (
              <div title={Drupal.t('Edit Address')} className="spc-address-tile-edit" onClick={(e) => this.openModal(e)}>
                <EditAddressSVG />
                <Popup
                  open={areaUpdated && isSelected ? true : open}
                  onClose={this.closeModal}
                  closeOnDocumentClick={false}
                >
                  <>
                    <AddressForm
                      closeModal={this.closeModal}
                      showEmail={false}
                      headingText={headingText}
                      show_prefered
                      default_val={editAddressData}
                      processAddress={this.processAddress}
                      fillDefaultValue
                    />
                  </>
                </Popup>
              </div>
              )}
          </div>
        </div>
      </div>
    );
  }
}
