import React from 'react';

import Popup from 'reactjs-popup';
import ShippingMethods from '../shipping-methods';
import AddressForm from '../address-form';
import { checkoutAddressProcess } from '../../../utilities/checkout_address_process';

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

  processAddress = (e) => {
    const { cart } = this.props.cart;
    checkoutAddressProcess(e, cart.cart_id);
  }

  componentDidMount() {
    document.addEventListener('refreshCartOnAddress', (e) => {
      var data = e.detail.data();
      // Close the modal.
      this.closeModal();
      this.props.refreshCart(data);
    }, false);
  }

  render() {
    const address = this.props.cart.address;
    return (
      <div className='delivery-information-preview'>
        <div className='spc-delivery-customer-info'>
          <div className='delivery-name'>{address.static.firstname} {address.static.lastname}</div>
          <div className='delivery-address'>
            {address.address_block_segment}
            , {address.address_building_segment}
            , {address.address_apartment_segment}
            , {address.city}
          </div>
          <div className='spc-address-form-edit-link' onClick={this.openModal}>
            {Drupal.t('Change')}
          </div>
        <Popup
          open={this.state.open}
          onClose={this.closeModal}
          closeOnDocumentClick={false}
        >
        <div className="modal">
          <a className="close" onClick={this.closeModal}>&times;</a>
          <AddressForm default_val={address}  processAddress={this.processAddress}/>
        </div>
        <div className='spc-delivery-shipping-methods'>
          <ShippingMethods cart={this.props.cart} refreshCart={this.props.refreshCart}/>
      </div>
      </Popup>
      </div>
      </div>
    );
  }

}
