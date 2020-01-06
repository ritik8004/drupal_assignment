import React from 'react';

export default class EmptyDeliveryText extends React.Component {

  render() {
  	if (this.props.delivery_type === 'cnc') {
  	  return (
      	<div>{Drupal.t('Select your prefered collection store')}</div>
      );
  	}

  	return (
      <div>{Drupal.t('Add your address and contact details')}</div>
    );
  }

}
