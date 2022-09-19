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
  // @todo: FE to check and use new logos for the cart page. It can be done
  // with the existing PaymentMethodIcon component passing the conext of cart
  // or checkout pages. But if required, we can have a new component for this.
  const methodIcons = Object.values(paymentMethods).map((method) => (
    <PaymentMethodIcon
      methodName={method.code}
      methodLabel={method.name}
    />
  ));

  // Return the block with full markup.
  return (
    <>
      <div className="spc-cart-payment-method-logos-block">
        <div className="block-title">
          {Drupal.t(
            'Checkout quickly and securely with',
            {},
            { context: 'cart_payment_logos' },
          )}
        </div>
        <div className="block-content">{methodIcons}</div>
      </div>
    </>
  );
});

export default PaymentMethodsLogos;
