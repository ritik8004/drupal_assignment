import React from 'react';

import {
  updateUserDefaultAddress,
  deleteUserAddress
} from '../../../utilities/address_util';

export default class AddressItem extends React.Component {

  /**
   * When user changes address.
   */
  changeDefaultAddress = (id) => {
    let addressList = updateUserDefaultAddress(id);
    if (addressList instanceof Promise) {
      addressList.then((response) => {
        if (response.status === true) {
          document.getElementById('address-' + id).checked = true;
          // Refresh the address list.
          this.props.refreshAddressList(response.data);
        }
      });
    }
  }

  /**
   * Deletes the user address.
   */
  deleteAddress = (id) => {
    let addressList = deleteUserAddress(id);
    if (addressList instanceof Promise) {
      addressList.then((response) => {
        if (response.status === true) {
          // Refresh the address list.
          this.props.refreshAddressList(response.data);
        }
      });
    }
  }

  /**
   * Edit address handler.
   */
  editAddress = (id) => {
    // Address edit will be here.
  }

  render() {
    const { address } = this.props;
    let addressData = [];
    Object.entries(window.drupalSettings.address_fields).forEach(([key, val]) => {
      addressData.push(<span key={key}>{address[key]}</span>)
    })

    return (
      <React.Fragment>
        <div>{address.given_name} {address.family_name}</div>
        <div>{addressData}</div>
        <div>+{window.drupalSettings.country_mobile_code} {address.mobile.local_number}</div>
        <div className='address delivery-method' onClick={() => this.changeDefaultAddress(address['address_id'])}>
          <input
            id={'address-' + address['address_id']}
            type='radio'
            defaultChecked={address['is_default'] === true}
            value={address['address_id']}
            name='address-book-address'/>

          <label className='radio-sim radio-label'>
            {Drupal.t('Preferred address')}
          </label>
        </div>
        <div>
          <button title={Drupal.t('Edit')} id={'address-edit-' + address['address_id']} onClick={() => {this.editAddress(address['address_id'])}}>{Drupal.t('Edit')}</button>
        </div>
        {address['is_default'] === false &&
          <div>
            <button title={Drupal.t('Delete')} id={'address-delete-' + address['address_id']} onClick={() => {this.deleteAddress(address['address_id'])}}>{Drupal.t('remove')}</button>
          </div>
        }
      </React.Fragment>
    );
  }

}
