import React from 'react';

import Price from '../../../utilities/price';

export default class SingleShippingMethod extends React.Component {

  render () {
    const { method } = this.props;
    let price = Drupal.t('FREE');
    if (method.amount > 0) {
      price = <Price price={method.amount}/>
    }

  	return(
      <div className='single-shipping-method'>
        <span>{method.carrier_title} {method.method_title}</span>
        <span>{price}</span>
      </div>
    );
  }

}
