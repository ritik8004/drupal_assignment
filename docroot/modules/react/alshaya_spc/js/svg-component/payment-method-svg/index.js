import React from 'react';
import BenefitPaySVG from './components/benefit-pay-svg';

const PaymentMethodIcon = (props) => {
  const { methodName, methodLabel, context } = props;
  const basePath = '/themes/custom/transac/alshaya_white_label/imgs/cards/payment-options-icons/';

  if (methodName === 'banktransfer') {
    return (
      <img className="payment-method-icon" src={`${basePath}bank-transfer.svg`} />
    );
  }

  if (methodName === 'checkout_com'
    || methodName === 'checkout_com_upapi') {
    // Rendering both Visa and Mastercard icons together if context is cart.
    if (context && context === 'cart') {
      return (
        <>
          <img className="payment-method-icon" src={`${basePath}visa.svg`} />
          <img className="payment-method-icon" src={`${basePath}mastercard.svg`} />
        </>
      );
    }

    return (
      <img className="payment-method-icon" src={`${basePath}card.svg`} />
    );
  }
  if (methodName === 'checkout_com_upapi_knet') {
    return (
      <img className="payment-method-icon" src={`${basePath}knet.svg`} />
    );
  }
  if (methodName === 'checkout_com_applepay' || methodName === 'checkout_com_upapi_applepay') {
    return (
      <img className="payment-method-icon" src={`${basePath}apple-pay.svg`} />
    );
  }
  if (methodName === 'checkout_com_upapi_qpay') {
    return (
      <img className="payment-method-icon" src={`${basePath}naps.svg`} />
    );
  }

  if (methodName === 'postpay') {
    return (
      // SVG had font issue and JPG not available so used png.
      <img className="payment-method-icon" src={`${basePath}post-pay.png`} />
    );
  }

  if (methodName === 'tabby') {
    return (
      <img className="payment-method-icon" src={`${basePath}tabby.svg`} alt={methodLabel} />
    );
  }

  if (methodName === 'checkout_com_upapi_fawry') {
    return (
      <img className="payment-method-icon" src={`${basePath}fawry-pay.svg`} />
    );
  }

  if (methodName === 'checkout_com_upapi_benefitpay') {
    return (
      <BenefitPaySVG />
    );
  }

  if (methodName === 'tamara') {
    // We do not have Tamara logo in SVG format, using JPG instead.
    return <img src="/themes/custom/transac/alshaya_white_label/imgs/icons/tamara.jpg" className="tamara-icon payment-method-icon" />;
  }

  return (
    <img className="payment-method-icon" src={`${basePath}cash.svg`} />
  );
};

export default PaymentMethodIcon;
