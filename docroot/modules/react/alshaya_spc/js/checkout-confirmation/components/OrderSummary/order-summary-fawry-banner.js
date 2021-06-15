import React from 'react';
import parse from 'html-react-parser';
import PaymentMethodIcon from '../../../svg-component/payment-method-svg';
import getStringMessage from '../../../utilities/strings';

const OrderSummaryFawryBanner = (props) => {
  const {
    animationDelay: animationDelayValue,
  } = props;

  const value = parse(getStringMessage('fawry_payment_option_confirmation_description'));

  return (
    <div className="spc-order-summary-item order-summary-banner-fawry fadeInUp" style={{ animationDelay: animationDelayValue }}>
      <span>{value}</span>
      <span><PaymentMethodIcon methodName="checkout_com_upapi_fawry" /></span>
    </div>
  );
};

export default OrderSummaryFawryBanner;
