import React from 'react';

import Popup from 'reactjs-popup';
import AddressForm from '../address-form';
import GoogleMap from '../../../utilities/map/GoogleMap';

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
      <div>
        <div onClick={this.openModal} className="spc-checkout-empty-delivery-text">
          {Drupal.t('Please add yor contact details and address.')}
        </div>
        <Popup
          open={this.state.open}
          onClose={this.closeModal}
          closeOnDocumentClick={false}
        >
        <div className="modal">
          <a className="close" onClick={this.closeModal}>&times;</a>
          <GoogleMap/>
          <AddressForm default_val={null} handleAddressData={this.props.handleAddressData} cart={this.props.cart}/>
        </div>
        </Popup>
      </div>  
    );
  }

}
