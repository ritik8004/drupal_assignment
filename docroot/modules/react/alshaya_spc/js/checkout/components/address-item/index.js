import React from 'react';

import Popup from 'reactjs-popup';
import AddressForm from '../address-form';
import {
  updateUserDefaultAddress,
  deleteUserAddress,
  addEditAddressToCustomer
} from '../../../utilities/address_util';
import {
  addShippingInCart
} from '../../../utilities/checkout_util';
import {
  showFullScreenLoader,
  removeFullScreenLoader
} from "../../../utilities/checkout_util";
import EditAddressSVG from "../edit-address-svg";

export default class AddressItem extends React.Component {

  constructor(props) {
    super(props);
    this.state = {
      open: false
    };
  }

  openModal = () => {
    this.setState({
      open: true
    });
  }

  closeModal = () => {
    this.setState({
      open: false
    });
  };

  /**
   * When user changes address.
   */
  changeDefaultAddress = (address) => {
    // Show loader.
    showFullScreenLoader();
    let addressList = updateUserDefaultAddress(address['address_id']);
    if (addressList instanceof Promise) {
      addressList.then((response) => {
        if (response.status === 200 && response.data.status === true) {
          document.getElementById('address-' + address['address_id']).checked = true;
          // Refresh the address list.
          let data = {
            'address_id': address['address_mdc_id'],
            'country_id': window.drupalSettings.country_code
          };
          var cart_info = addShippingInCart('update shipping', data);
          if (cart_info instanceof Promise) {
            cart_info.then((cart_result) => {
              // Remove loader.
              removeFullScreenLoader();
              // If no error.
              if (cart_result.error === undefined) {
                let cart_data = {
                  'cart': cart_result
                }
                var event = new CustomEvent('refreshCartOnAddress', {
                  bubbles: true,
                  detail: {
                    data: () => cart_data
                  }
                });
                document.dispatchEvent(event);

                this.props.refreshAddressList(response.data);
              }
            });
          }
        }
        else {
          // Remove the loader.
          removeFullScreenLoader();
        }
      });
    }
  };

  /**
   * Deletes the user address.
   */
  deleteAddress = (id) => {
    // Show loader.
    showFullScreenLoader()
    let addressList = deleteUserAddress(id);
    if (addressList instanceof Promise) {
      addressList.then((response) => {
        removeFullScreenLoader();
        if (response.status === 200 && response.data.status === true) {
          // Refresh the address list.
          this.props.refreshAddressList(response.data);
        }
      });
    }
  }

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
    const mobile_value = address.mobile.value;
    const mob_default_val = mobile_value.replace('+' + window.drupalSettings.country_mobile_code, '');
    let addressData = [];
    let editAddressData = {};
    Object.entries(window.drupalSettings.address_fields).forEach(([key, val]) => {
      addressData.push(<span key={key}>{address[key]}, </span>)
      editAddressData[val['key']] = address[key];
    })

    editAddressData['static'] = {};
    editAddressData['static']['fullname'] = address['given_name'] + ' ' + address['family_name'];
    editAddressData['static']['telephone'] = mob_default_val;
    editAddressData['static']['address_id'] = address['address_id'];

    return (
      <div className='spc-address-tile'>
      <div className='spc-address-metadata'>
        <div className='spc-address-name'>{address.given_name} {address.family_name}</div>
        <div className='spc-address-fields'>{addressData}</div>
        <div className='spc-address-mobile'>+{window.drupalSettings.country_mobile_code} {mob_default_val}</div>
      </div>
      <div className='spc-address-tile-actions'>
        <div className='spc-address-preferred default-address' onClick={() => this.changeDefaultAddress(address)}>
          <input
            id={'address-' + address['address_id']}
            type='radio'
            defaultChecked={address['is_default'] === true}
            value={address['address_id']}
            name='address-book-address'/>

          <label className='radio-sim radio-label'>
            {Drupal.t('preferred address')}
          </label>
        </div>
        <div className='spc-address-btns'>
          <div title={Drupal.t('Edit Address')} className='spc-address-tile-edit' onClick={this.openModal}>
            <EditAddressSVG/>
            <Popup open={this.state.open} onClose={this.closeModal} closeOnDocumentClick={false}>
              <React.Fragment>
                <a className='close' onClick={this.closeModal}>&times;</a>
                <AddressForm showEmail={false} show_prefered={true} default_val={editAddressData} processAddress={this.processAddress} />
              </React.Fragment>
            </Popup>
          </div>
          {address['is_default'] !== true &&
            <div className='spc-address-tile-delete-btn'>
              <button title={Drupal.t('Delete Address')}  id={'address-delete-' + address['address_id']} onClick={() => {this.deleteAddress(address['address_id'])}}>{Drupal.t('remove')}</button>
            </div>
          }
        </div>
      </div>
      </div>
    );
  }

}
