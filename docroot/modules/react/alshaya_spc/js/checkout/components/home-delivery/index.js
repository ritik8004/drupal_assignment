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
      <div>
        <div>{static_hd_info.firstname} {static_hd_info.lastname}</div>
        <span>{static_hd_info.email}</span> <span>{static_hd_info.telephone}</span>
        <div onClick={this.openModal}>
          {Drupal.t('Change')}
        </div>
        <Popup
          open={this.state.open}
          onClose={this.closeModal}
          closeOnDocumentClick={false}
        >
        <div className="modal">
          <a className="close" onClick={this.closeModal}>&times;</a>
        <AddressForm default_val={this.props.hd_info} handleAddressData={this.props.handleAddressData} cart={this.props.cart}/>
        </div>
        </Popup>
        <div>
          <ShippingMethods shipping_methods={this.props.methods}/>
        </div>
      </div>
    );
  }

}
