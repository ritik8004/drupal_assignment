import React from 'react';

import Popup from 'reactjs-popup';
import AddressForm from '../address-form';
import {
  updateUserDefaultAddress,
  addEditAddressToCustomer,
  gerAreaLabelById
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
   * Prepare address data to update shipping when
   */
  prepareAddressToUpdate = (address) => {
    let data = {};
    data.static = {
      firstname: address.given_name,
      lastname: address.family_name,
      email: address.email,
      city: gerAreaLabelById(false, address['administrative_area']),
      country_id: window.drupalSettings.country_code,
      customer_address_id: address.address_mdc_id,
      customer_id: address.customer_id
    };

    // Getting dynamic fields data.
    Object.entries(window.drupalSettings.address_fields).forEach(([key, field]) => {
      data[field.key] = address[key];
    });

    return data;
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
          let data = this.prepareAddressToUpdate(address);
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
      let fillVal = address[key];
      // Handling for area field.
      if (key === 'administrative_area') {
        fillVal = address['area_label'];
      }
      // Handling for parent area.
      else if (key === 'area_parent') {
        fillVal = address['area_parent_label'];
      }

      addressData.push(<span key={key}>{fillVal}, </span>)
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
        </div>
      </div>
      </div>
    );
  }

}
