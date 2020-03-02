import React from 'react';

import ShippingMethod from '../shipping-method';

export default class ShippingMethods extends React.Component {
  render() {
    const { cart, refreshCart } = this.props;
    const methods = [];
    Object.entries(cart.ShippingMethods).forEach(([key, method]) => {
      // Don't show CNC in HD methods.
      if (method.carrier_code === window.drupalSettings.cnc.cnc_shipping.code) {
        return;
      }

      const carrirerInfo = `${method.carrier_code}_${method.method_code}`;
      const selected = cart.cart.carrier_info === carrirerInfo
        ? method.method_code
        : '';
      methods.push(<ShippingMethod
        selected={selected}
        key={key}
        method={method}
        cart={cart}
        refreshCart={refreshCart}
      />);
    });

    return (
      <div className={`shipping-methods shipping-methods-${cart.shippingMethods.length}`}>
        {methods}
      </div>
    );
  }
}
