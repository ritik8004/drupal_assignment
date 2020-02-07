import React from 'react';

import Popup from 'reactjs-popup';
import AddressContent from "../address-popup-content";
import { checkoutAddressProcess } from '../../../utilities/checkout_address_process';
import ClickCollect from '../click-collect';

export default class EmptyDeliveryText extends React.Component {

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

  componentDidMount() {
    document.addEventListener('refreshCartOnAddress', (e) => {
      var data = e.detail.data();
      this.props.refreshCart(data);
      // Close the modal.
      this.closeModal();
    }, false);
  }

  getAddressPopupClassName = () => {
    return window.drupalSettings.user.uid > 0 ? 'spc-address-list-member' : 'spc-address-form-guest';
  };

  /**
   * Process the address form data on sumbit.
   */
  processAddress = (e) => {
    const { cart } = this.props.cart;
    checkoutAddressProcess(e, cart);
  }

  render() {
    const { delivery_type } = this.props.cart;
    if (delivery_type === 'cnc') {
  	  return (
        <div className='spc-empty-delivery-information'>
          <div onClick={this.openModal} className="spc-checkout-empty-delivery-text">
            {Drupal.t('Select your preferred collection store')}
          </div>
          <Popup open={this.state.open} onClose={this.closeModal} closeOnDocumentClick={false}>
            <a className='close' onClick={this.closeModal}>&times;</a>
            <ClickCollect default_val={null} processAddress={this.processAddress}/>
          </Popup>
        </div>
      );
  	}

  	return (
      <div className='spc-empty-delivery-information'>
        <div onClick={this.openModal} className="spc-checkout-empty-delivery-text">
          {Drupal.t('Please add your contact details and address.')}
        </div>
        <Popup className={this.getAddressPopupClassName()} open={this.state.open} onClose={this.closeModal} closeOnDocumentClick={false}>
          <React.Fragment>
            <a className='close' onClick={this.closeModal}>&times;</a>
            <AddressContent/>
          </React.Fragment>
        </Popup>
      </div>
    );
  }

}
