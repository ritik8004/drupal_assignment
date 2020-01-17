import React from 'react';

import SectionTitle from '../../../utilities/section-title';
import PaymentMethod from '../payment-method';

export default class PaymentMethods extends React.Component {

  render() {
    let methods = [];
    Object.entries(this.props.payment_methods).forEach(([key, method]) => {
      methods.push(<PaymentMethod key={key} method={method} payment_method_select={this.props.payment_method_select} />);
    });

    let active_class = this.props.is_active
      ? 'active'
      : 'in-active';

    return(
      <div className="spc-checkout-payment-options">
      	<SectionTitle>{Drupal.t('Payment methods')}</SectionTitle>
      	<div className={'payment-methods ' + active_class}>{methods}</div>
      </div>
    );
  }

}
