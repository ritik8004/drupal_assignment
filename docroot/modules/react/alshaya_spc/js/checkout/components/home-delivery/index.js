import React from 'react';

import Popup from 'reactjs-popup';
import ShippingMethods from '../shipping-methods';
import AddressForm from '../address-form';

export default class HomeDeliveryInfo extends React.Component {

  constructor(props) {
    super(props);
    this.state = { open: false };
  }

  openModal = () => {
    this.setState({ open: true });
  }

  closeModal = () => {
    this.setState({ open: false });
  }

  render() {
    let static_hd_info = this.props.hd_info;
    return (
      <div className='delivery-information-preview'>
        <div className='delivery-name'>{static_hd_info.firstname} {static_hd_info.lastname}</div>
        <span className='delivery-email'>{static_hd_info.email}</span> <span className='delivery-tel'>{static_hd_info.telephone}</span>
        <div className='spc-address-form-edit-link' onClick={this.openModal}>
          {Drupal.t('Change')}
        </div>
        <Popup open={this.state.open} onClose={this.closeModal} closeOnDocumentClick={false}>
          <a className="close" onClick={this.closeModal}></a>
          <AddressForm default_val={this.props.hd_info} handleAddressData={this.props.handleAddressData} cart={this.props.cart}/>
        </Popup>
        <div className='spc-delivery-shipping-methods'>
          <ShippingMethods cart={this.props.cart} shipping_methods={this.props.methods}/>
        </div>
      </div>
    );
  }

}
