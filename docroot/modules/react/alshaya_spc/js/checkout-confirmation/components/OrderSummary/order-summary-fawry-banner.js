import React from 'react';
import PaymentMethodIcon from '../../../svg-component/payment-method-svg';

const OrderSummaryFawryBanner = (props) => {
  const {
    animationDelay: animationDelayValue,
  } = props;

  const value = 'Please complete your payment at the nearest Fawry Cash Point using your reference number.';

  return (
    <div className="spc-order-summary-item order-summary-banner-fawry fadeInUp" style={{ animationDelay: animationDelayValue }}>
      <span>{value}</span>
      <span><PaymentMethodIcon methodName="checkout_com_upapi_fawry" /></span>
    </div>
  );
};

export default OrderSummaryFawryBanner;
