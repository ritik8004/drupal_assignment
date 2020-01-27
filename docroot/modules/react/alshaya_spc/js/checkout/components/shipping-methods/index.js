import React from 'react';

import ShippingMethod from '../shipping-method';
import SingleShippingMethod from '../single-shipping-method';

export default class ShippingMethods extends React.Component {

  constructor(props) {
    super(props);
    this.state = {
      'shipping_method': null
    };
  }

  render() {
    const shipping_methods = this.props.cart.shipping_methods;
    // For single shipping method case.
    if (shipping_methods.length === 1) {
      return (
         <div className='shipping-methods'>
           <SingleShippingMethod method={shipping_methods[0]}/>
         </div>
      );
    }

    let methods = [];
    Object.entries(shipping_methods).forEach(([key, method]) => {
      methods.push(<ShippingMethod key={key} method={method} refreshCart={this.props.refreshCart}/>);
    });

    return (
      <div className='shipping-methods'>
      	{methods}
      </div>
    );
  }

}
