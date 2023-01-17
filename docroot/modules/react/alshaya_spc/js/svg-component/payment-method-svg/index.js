import React from 'react';
import BenefitPaySVG from './components/benefit-pay-svg';

const PaymentMethodIcon = (props) => {
  const { methodName, methodLabel, context } = props;
  if (methodName === 'banktransfer') {
    return (
      <img className="payment-method-icon" src="/modules/react/alshaya_spc/assets/svg/bank-transfer.svg" />
    );
  }

  if (methodName === 'checkout_com'
    || methodName === 'checkout_com_upapi') {
    // Rendering both Visa and Mastercard icons together if context is cart.
    if (context && context === 'cart') {
      return (
        <>
          <img className="payment-method-icon" src="/modules/react/alshaya_spc/assets/svg/visa.svg" />
          <img className="payment-method-icon" src="/modules/react/alshaya_spc/assets/svg/mastercard.svg" />
        </>
      );
    }

    return (
      <img className="payment-method-icon" src="/modules/react/alshaya_spc/assets/svg/card.svg" />
    );
  }
  if (methodName === 'checkout_com_upapi_knet') {
    return (
      <img className="payment-method-icon" src="/modules/react/alshaya_spc/assets/svg/knet.svg" />
    );
  }
  if (methodName === 'checkout_com_applepay' || methodName === 'checkout_com_upapi_applepay') {
    return (
      <img className="payment-method-icon" src="/modules/react/alshaya_spc/assets/svg/apple-pay.svg" />
    );
  }
  if (methodName === 'checkout_com_upapi_qpay') {
    return (
      <img className="payment-method-icon" src="/modules/react/alshaya_spc/assets/svg/naps.svg" />
    );
  }

  if (methodName === 'postpay') {
    return (
      <img className="payment-method-icon" src="/modules/react/alshaya_spc/assets/svg/post-pay.svg" />
    );
  }

  if (methodName === 'tabby') {
    return (
      <img className="payment-method-icon" src="/modules/react/alshaya_spc/assets/svg/tabby.svg" alt={methodLabel} />
    );
  }

  if (methodName === 'checkout_com_upapi_fawry') {
    return (
      <img className="payment-method-icon" src="/modules/react/alshaya_spc/assets/svg/fawry-pay.svg" />
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
    <img className="payment-method-icon" src="/modules/react/alshaya_spc/assets/svg/cash.svg" />
  );
};

export default PaymentMethodIcon;
