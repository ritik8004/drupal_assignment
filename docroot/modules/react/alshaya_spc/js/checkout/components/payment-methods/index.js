import React from 'react';

import CheckoutSectionTitle from '../../../cart/components/spc-checkout-section-title';

export default class PaymentMethods extends React.Component {

  render() {
    return(
      <div>
      	<CheckoutSectionTitle>{Drupal.t('Payment methods')}</CheckoutSectionTitle>
      	<div>Payment methods here</div>
      </div>
    );
  }

}
