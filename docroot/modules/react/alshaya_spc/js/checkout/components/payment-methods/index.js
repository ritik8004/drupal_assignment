import React from 'react';

import CheckoutSectionTitle from '../../../cart/components/spc-checkout-section-title';
import PaymentMethod from '../payment-method';

export default class PaymentMethods extends React.Component {

  render() {
  	let methods = [];
  	Object.entries(window.drupalSettings.payment_methods).forEach(([key, method]) => {
      methods.push(<PaymentMethod key={key} method={method}/>);
    });

    let active_class = this.props.is_active
      ? 'active'
      : 'in-active';

    return(
      <div>
      	<CheckoutSectionTitle>{Drupal.t('Payment methods')}</CheckoutSectionTitle>
      	<div className={'payment-method ' + active_class}>{methods}</div>
      </div>
    );
  }

}
