import React from 'react';

import Popup from 'reactjs-popup';
import AddressForm from '../address-form';

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

  render() {
    if (this.props.delivery_type === 'cnc') {
  	  return (
      	<div className="spc-checkout-empty-delivery-text">{Drupal.t('Select your preferred collection store')}</div>
      );
  	}

  	return (
      <div className='spc-empty-delivery-information'>
        <div onClick={this.openModal} className="spc-checkout-empty-delivery-text">
          {Drupal.t('Please add yor contact details and address.')}
        </div>
        <Popup open={this.state.open} onClose={this.closeModal} closeOnDocumentClick={false}>
          <a className="close" onClick={this.closeModal}>&times;</a>
          <AddressForm default_val={null} handleAddressData={this.props.handleAddressData} cart={this.props.cart}/>
        </Popup>
      </div>
    );
  }

}
