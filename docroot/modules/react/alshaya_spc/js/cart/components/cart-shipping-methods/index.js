import React from 'react';

/**
 * Shipping method tag component function.
 */
const ShippingMethodTag = (shippingMethod) => (
  <div key={shippingMethod.carrier_code} className={`cart-shipping-method ${shippingMethod.carrier_code.toString().toLowerCase()} ${shippingMethod.available ? 'active' : 'in-active'}`}>
    <span className="carrier-title">{shippingMethod.cart_page_title}</span>
    <span className="information-icon">
      <span className="method-title">
        <span>{shippingMethod.method_title}</span>
      </span>
    </span>
  </div>
);

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
      <div className="shipping-tags-first-row">
        {
          shippingMethods.map(ShippingMethodTag)
        }
      </div>
    </div>
  );
};

export default CartShippingMethods;
