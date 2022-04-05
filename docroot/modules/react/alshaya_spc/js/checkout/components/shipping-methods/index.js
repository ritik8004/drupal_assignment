import React from 'react';

import ShippingMethod from '../shipping-method';

const ShippingMethods = ({ cart, refreshCart, shippingInfoUpdated }) => {
  if (!cart.cart.shipping.methods) {
    return null;
  }

  const methods = [];
  Object.entries(cart.cart.shipping.methods).forEach(([key, method]) => {
    // Don't show CNC in HD methods.
    if (method.carrier_code === window.drupalSettings.map.cnc_shipping.code) {
      return;
    }

    const carrirerInfo = `${method.carrier_code}_${method.method_code}`;
    // After recent change in api response, need to check if method is applicable.
    let selected = '';
    if (cart.cart.shipping.method === carrirerInfo && method.available) {
      selected = method.method_code;
    } else {
      // If last order method not available, then find the first available method.
      const selectedMethod = cart.cart.shipping.methods.find(
        (element) => element.available === true,
      );
      selected = selectedMethod.method_code;
    }

    methods.push(<ShippingMethod
      shippingInfoUpdated={shippingInfoUpdated}
      selected={selected}
      key={key}
      method={method}
      cart={cart}
      refreshCart={refreshCart}
    />);
  });

  return (
    <div className={`shipping-methods shipping-methods-${cart.cart.shipping.methods.length}`}>
      {methods}
    </div>
  );
};

export default ShippingMethods;
