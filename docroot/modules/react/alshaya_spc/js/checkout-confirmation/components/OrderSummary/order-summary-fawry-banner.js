import React from 'react';
import parse from 'html-react-parser';
import PaymentMethodIcon from '../../../svg-component/payment-method-svg';

const OrderSummaryFawryBanner = (props) => {
  const {
    animationDelay: animationDelayValue,
  } = props;

  const value = parse(Drupal.t("Pay for your order through any of <a href='#' target='_blank'>Fawry's cash points</a> at your convenient time and location across Egypt."));

  return (
    <div className="spc-order-summary-item order-summary-banner-fawry fadeInUp" style={{ animationDelay: animationDelayValue }}>
      <span>{value}</span>
      <span><PaymentMethodIcon methodName="checkout_com_upapi_fawry" /></span>
    </div>
  );
};

export default OrderSummaryFawryBanner;
