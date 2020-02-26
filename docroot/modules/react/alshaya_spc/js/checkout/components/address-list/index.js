import React from 'react';

import Popup from 'reactjs-popup';
import AddressItem from '../address-item';
import AddressForm from '../address-form';
import { getUserAddressList, addNewUserAddress } from '../../../utilities/address_util';

export default class AddressList extends React.Component {

  constructor(props) {
    super(props);
    this.state = {
      addressList: [],
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

  componentDidMount() {
    // If user is logged in, only then get area lists.
    if (window.drupalSettings.user.uid > 0) {
      let addressList = getUserAddressList();
      if (addressList instanceof Promise) {
        addressList.then((list) => {
          this.setState({
            addressList: list
          });
        });
      }
    }
  }

  refreshAddressList = (addressList) => {
    this.setState({
      addressList: addressList
    });
  };

  /**
   * Process add new address.
   */
  processAddress = (e) => {
    let form_data = {};
    form_data['address'] = {
      'given_name': e.target.elements.fname.value,
      'family_name': e.target.elements.lname.value,
      'city': 'Dummy Value',
      'address_id': null
    };

    form_data['mobile'] = e.target.elements.mobile.value

    // Getting dynamic fields data.
    Object.entries(window.drupalSettings.address_fields).forEach(([key, field]) => {
      form_data['address'][key] = e.target.elements[key].value
    });

    let addressList = addNewUserAddress(form_data);
    if (addressList instanceof Promise) {
      addressList.then((list) => {
        // Close the addresslist popup.
        let event = new CustomEvent('closeAddressListPopup', {
          bubbles: true,
          detail: {
            close: () => true
          }
        });
        document.dispatchEvent(event);
        // Close the address modal.
        this.closeModal();
      });
    }

  };

  render() {
    // If no address list available.
    if (this.state.addressList === undefined
      || this.state.addressList.length === 0) {
      return (null);
    }

    let addressItem = [];
    Object.entries(this.state.addressList).forEach(([key, address]) => {
      addressItem.push(<AddressItem key={key} address={address} refreshAddressList={this.refreshAddressList} />);
    });

    let default_val = {
      'static': {
        'firstname': window.drupalSettings.user_name.fname,
        'lastname': window.drupalSettings.user_name.lname
      }
    }

    return (
      <React.Fragment>
        <header className='spc-change-address'>{Drupal.t('change address')}</header>
        <div className='address-list-content'>
          <div className='spc-add-new-address-btn' onClick={this.openModal}>
            {Drupal.t('Add new address')}
          </div>
          <Popup open={this.state.open} onClose={this.closeModal} closeOnDocumentClick={false}>
            <React.Fragment>
              <a className='close' onClick={this.closeModal}>&times;</a>
              <AddressForm show_prefered={true} default_val={default_val} processAddress={this.processAddress} />
            </React.Fragment>
          </Popup>
          <div className='spc-checkout-address-list'>{addressItem}</div>
        </div>
      </React.Fragment>
    );
  }

}
