import React from 'react';

import Price from '../../../utilities/price';

export default class SingleShippingMethod extends React.Component {

  render () {
    let method = this.props.method;
    let price = Drupal.t('FREE');
    if (method.amount > 0) {
      price = <Price price={method.amount}/>
    }

  	return(
      <div className='single-shipping-method'>
      	<span>{this.props.method.carrier_title} {this.props.method.method_title}</span>
        <span>{price}</span>
      </div>
    );
  }

}
