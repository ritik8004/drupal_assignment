import React from 'react';

import CheckoutSectionTitle from '../../../cart/components/spc-checkout-section-title';
import EmptyDeliveryText from '../empty-delivery';

export default class DeliveryInformation extends React.Component {

  render() {
    return (
      <div>
        <CheckoutSectionTitle>{Drupal.t('Delivery information')}</CheckoutSectionTitle>
        <EmptyDeliveryText />
      </div>
    );
  }

}
