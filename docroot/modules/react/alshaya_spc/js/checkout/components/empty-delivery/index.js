import React from 'react';

export default class EmptyDeliveryText extends React.Component {

  render() {
  	if (this.props.delivery_type === 'cnc') {
  	  return (
      	<div className="spc-checkout-empty-delivery-text">{Drupal.t('Select your preferred collection store')}</div>
      );
  	}

  	return (
      <div className="spc-checkout-empty-delivery-text">{Drupal.t('Add your address and contact details')}</div>
    );
  }

}
