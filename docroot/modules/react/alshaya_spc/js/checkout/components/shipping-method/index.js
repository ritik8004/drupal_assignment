import React from 'react';

import Price from '../../../utilities/price';

export default class ShippingMethod extends React.Component {

  constructor(props) {
    super(props);
    this.state = {
      'selectedOption': ''
    };
  }

  render () {
    let method = this.props.method;
    let price = Drupal.t('FREE');
    if (method.amount > 0) {
      price = <Price price={method.amount}/>
    }
  	return(
      <div className='shipping-method' onClick={() => this.changeShippingMethod(method)}>
      	<input
      	  id={'shipping-method-' + method.method_code}
      	  className={method.method_code}
      	  type='radio'
      	  checked={this.state.selectedOption === method.method_code}
      	  value={method.method_code}
      	  name='shipping-method' />

        <label className='radio-sim radio-label'>
          <span>{this.props.method.carrier_title} {this.props.method.method_title}</span>
          <span>{price}</span>
        </label>
      </div>
    );
  }

}
