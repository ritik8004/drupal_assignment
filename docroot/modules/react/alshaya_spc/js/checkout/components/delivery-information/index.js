import React from 'react';

import CheckoutSectionTitle from '../../../cart/components/spc-checkout-section-title';
import EmptyDeliveryText from '../empty-delivery';

export default class DeliveryInformation extends React.Component {

  render() {
  	let title = this.props.delivery_type === 'cnc'
  	  ? Drupal.t('Collection store')
  	  : Drupal.t('Delivery information');
    return (
      <div>
        <CheckoutSectionTitle>{title}</CheckoutSectionTitle>
        <EmptyDeliveryText delivery_type={this.props.delivery_type} />
      </div>
    );
  }

}
