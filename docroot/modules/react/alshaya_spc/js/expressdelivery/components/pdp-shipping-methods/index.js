import React from 'react';

const PdpShippingMethods = ({
  shippingMethods,
}) => {
  if (shippingMethods === null) {
    return null;
  }
  return (
    <div className="product-shiiping-methods">
      {shippingMethods.map((shippingMethod) => (
        <div key={shippingMethod.carrier_code} className={`cart-shipping-method ${shippingMethod.carrier_code.toString().toLowerCase()} ${shippingMethod.available ? 'active' : 'in-active'}`}>
          <div className="shipping-method-detail-wrapper">
            <span className="carrier-title">{shippingMethod.carrier_title}</span>
            <span className="method-title">{shippingMethod.method_title}</span>
          </div>
        </div>
      ))}
    </div>
  );
};

export default PdpShippingMethods;
