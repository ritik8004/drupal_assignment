import React from 'react';

const CartShippingMethods = (props) => {
  const { cartShippingMethods, sku, parentSKU } = props;
  let shippingMethods = null;

  if (cartShippingMethods === null) {
    return null;
  }

  if (Array.isArray(cartShippingMethods) && cartShippingMethods.length !== 0) {
    const cartMethodsObj = cartShippingMethods.find(
      (element) => element.product_sku === sku || element.product_sku === parentSKU,
    );
    if (cartMethodsObj && Object.keys(cartMethodsObj).length !== 0) {
      shippingMethods = cartMethodsObj.applicable_shipping_methods;
    }
  }

  if (shippingMethods === null) {
    return null;
  }
  return (
    <div className="sku-cart-delivery-methods">
      {
        shippingMethods.map((shippingMethod) => (
          <div className={`cart-shipping-method ${shippingMethod.carrier_code.toString().toLowerCase()} ${shippingMethod.available ? 'active' : 'in-active'}`}>
            <span className="carrier-title">{shippingMethod.carrier_title}</span>
            <span className="method-title">{shippingMethod.method_title}</span>
          </div>
        ))
      }
    </div>
  );
};

export default CartShippingMethods;
