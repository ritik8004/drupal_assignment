import React from 'react';

import PriceElement from "../../../utilities/special-price/PriceElement";

export default class ShippingMethod extends React.Component {

  constructor(props) {
    super(props);
    this.state = {
      'selectedOption': this.props.selected
    };
  }

  changeShippingMethod = (method) => {
    this.setState({
      selectedOption: method.method_code
    });

    document.getElementById('shipping-method-' + method.method_code).checked = true;
  };

  render () {
    let method = this.props.method;
    let price = Drupal.t('FREE');
    if (method.amount > 0) {
      price = <PriceElement amount={method.amount}/>
    }
  	return(
      <div className='shipping-method' onClick={() => this.changeShippingMethod(method)}>
      	<input
      	  id={'shipping-method-' + method.method_code}
      	  className={method.method_code}
      	  type='radio'
      	  defaultChecked={this.state.selectedOption === method.method_code}
      	  value={method.method_code}
      	  name='shipping-method' />

        <label className='radio-sim radio-label'>
          <span className='carrier-title'>{this.props.method.carrier_title}</span>
          <span className='method-title'>{this.props.method.method_title}</span>
          <span className='spc-price'>{price}</span>
        </label>
      </div>
    );
  }

}
