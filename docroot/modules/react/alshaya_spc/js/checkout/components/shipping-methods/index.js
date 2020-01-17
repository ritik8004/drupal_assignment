import React from 'react';

import ShippingMethod from '../shipping-method';
import SingleShippingMethod from '../single-shipping-method';

export default class ShippingMethods extends React.Component {

  render() {
    let methods = [];
    Object.entries(this.props.shipping_methods).forEach(([key, method]) => {
      methods.push(<ShippingMethod key={key} method={method}/>);
    });

    // For single shipping method case.
    if (methods.length === 1) {
      return (
        <div className='shipping-methods'>
          <SingleShippingMethod method={this.props.shipping_methods[0]}/>
        </div>
      )
    }

    return (
      <div className='shipping-methods'>
      	<div>{methods}</div>
      </div>
    );
  }

}
