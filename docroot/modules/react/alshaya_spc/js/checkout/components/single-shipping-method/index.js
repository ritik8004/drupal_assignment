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
      <div className='single-shipping-method shipping-method'>
        <span className='carrier-title'>{method.carrier_title}</span>
        <span className='method-title'>{method.method_title}</span>
        <span className='spc-price'>{price}</span>
      </div>
    );
  }

}
