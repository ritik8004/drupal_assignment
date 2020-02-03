import React from 'react';

import Popup from 'reactjs-popup';
import AddressForm from '../address-form';
import {
  updateUserDefaultAddress,
  deleteUserAddress
} from '../../../utilities/address_util';

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
  }

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

  render() {
    const { address } = this.props;
    let addressData = [];
    let editAddressData = {};
    Object.entries(window.drupalSettings.address_fields).forEach(([key, val]) => {
      addressData.push(<span key={key}>{address[key]}</span>)
      editAddressData[val['key']] = address[key];
    })

    editAddressData['static'] = {};
    editAddressData['static']['firstname'] = address['given_name'];
    editAddressData['static']['lastname'] = address['family_name'];
    editAddressData['static']['telephone'] = address['mobile']['local_number'];

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
          <div onClick={this.openModal}>
            {Drupal.t('Edit')}
          </div>
          <Popup open={this.state.open} onClose={this.closeModal} closeOnDocumentClick={false}>
            <React.Fragment>
              <a className='close' onClick={this.closeModal}>&times;</a>
              <AddressForm showEmail={false} show_prefered={true} default_val={editAddressData}/>
            </React.Fragment>
          </Popup>
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
