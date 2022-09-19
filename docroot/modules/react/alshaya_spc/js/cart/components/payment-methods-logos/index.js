import React, { memo } from 'react';
import PaymentMethodIcon from '../../../svg-component/payment-method-svg';

/**
 * The component will return all the available payment methods logo block for
 * the cart page. We are showing all avaiable payment methods from
 * drupalSettings and passing those as props to effectively using React memo.
 * So this component will only loads again when props get changed.
 */
const PaymentMethodsLogos = memo((props) => {
  // Get the available payment methods from props.
  const { paymentMethods } = props;
  if (!paymentMethods) {
    return null;
  }

  // Prepare the logos for all payment method icons using existing icons.
  const methodIcons = Object.values(paymentMethods).map((method) => (
    // Using context prop in order to display Cart page specific icons for Visa and Mastercard.
    <PaymentMethodIcon
      methodName={method.code}
      methodLabel={method.name}
      context="cart"
    />
  ));

  // Return the block with full markup.
  return (
    <>
      <div className="spc-cart-payment-method-logos-block">
        <div className="spc-cart-payment-method-logos-block__title">
          {Drupal.t(
            'Checkout quickly and securely with',
            {},
            { context: 'cart_payment_logos' },
          )}
        </div>
        <div className="spc-cart-payment-method-logos-block__content">{methodIcons}</div>
      </div>
    </>
  );
});

export default PaymentMethodsLogos;
