import React from 'react';

import ShippingMethod from '../shipping-method';

export default class ShippingMethods extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      shipping_method: null,
    };
  }

  render() {
    const { cart } = this.props;
    const { shipping_methods } = cart.cart;
    const methods = [];
    Object.entries(shipping_methods).forEach(([key, method]) => {
      // Don't show CNC in HD methods.
      if (method.carrier_code === window.drupalSettings.cnc.cnc_shipping.code) {
        return;
      }

      const carrirer_info = `${method.carrier_code}_${method.method_code}`;
      const selected = cart.cart.carrier_info === carrirer_info
        ? method.method_code
        : '';
      methods.push(<ShippingMethod selected={selected} key={key} method={method} refreshCart={this.props.refreshCart} />);
    });

    return (
      <div className={`shipping-methods shipping-methods-${shipping_methods.length}`}>
        {methods}
      </div>
    );
  }
}
