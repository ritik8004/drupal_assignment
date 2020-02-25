import React from 'react';

import Popup from 'reactjs-popup';
import AddressItem from '../address-item';
import AddressForm from '../address-form';
import { getUserAddressList } from '../../../utilities/address_util';

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

  componentDidMount () {
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

  processAddress = (e) => {
    console.log('Address submission processing here');
  };

  render () {
    // If no address list available.
    if (this.state.addressList === undefined
      ||this.state.addressList.length === 0) {
      return (null);
    }

    let addressItem = [];
    Object.entries(this.state.addressList).forEach(([key, address]) => {
      addressItem.push( <AddressItem key={key} address={address} refreshAddressList={this.refreshAddressList}/>);
    });

    return (
      <React.Fragment>
        <header className='spc-change-address'>{Drupal.t('change address')}</header>
        <div className='address-list-content'>
          <div className='spc-add-new-address-btn' onClick={this.openModal}>
            {Drupal.t('Add new address')}
          </div>
          <Popup open={this.state.open} onClose={this.closeModal} closeOnDocumentClick={false}>
            <AddressForm closeModal= {this.closeModal} show_prefered={true} default_val={null} processAddress={this.processAddress}/>
          </Popup>
          <div className='spc-checkout-address-list'>{addressItem}</div>
        </div>
      </React.Fragment>
    );
  }

}
