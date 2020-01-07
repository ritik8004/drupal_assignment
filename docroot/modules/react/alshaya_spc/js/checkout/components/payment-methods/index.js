import React from 'react';

import SectionTitle from '../../../utilities/section-title';
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
      	<SectionTitle>{Drupal.t('Payment methods')}</SectionTitle>
      	<div className={'payment-method ' + active_class}>{methods}</div>
      </div>
    );
  }

}
