import React from 'react';
import ConditionalView from '../../../common/components/conditional-view';
import CodSurchargePaymentMethodDescription
  from '../payment-description-cod-surchage';
import PaymentMethodCheckoutCom from '../payment-method-checkout-com';
import PaymentMethodIcon from '../../../svg-component/payment-method-svg';
import { addPaymentMethodInCart } from '../../../utilities/update_cart';
import {
  placeOrder,
  removeFullScreenLoader,
  showFullScreenLoader,
} from '../../../utilities/checkout_util';
import CheckoutComContextProvider from '../../../context/CheckoutCom';
import PaymentMethodCybersource from '../payment-method-cybersource';
import { removeStorageInfo } from '../../../utilities/storage';

export default class PaymentMethod extends React.Component {
  constructor(props) {
    super(props);

    this.paymentMethodCheckoutCom = React.createRef();
    this.paymentMethodCybersource = React.createRef();
  }

  validateBeforePlaceOrder = () => {
    const { method } = this.props;
    // Do additional process for some payment methods.
    if (method.code === 'checkout_com') {
      return this.paymentMethodCheckoutCom.current.validateBeforePlaceOrder();
    }

    if (method.code === 'cybersource') {
      return this.paymentMethodCybersource.current.validateBeforePlaceOrder();
    }

    if (method.code === 'knet') {
      showFullScreenLoader();

      const paymentData = {
        payment: {
          method: 'knet',
          additional_data: {},
        },
      };

      this.finalisePayment(paymentData);
      return false;
    }

    return true;
  };

  finalisePayment = (paymentData) => {
    addPaymentMethodInCart('finalise payment', paymentData).then((result) => {
      if (result.error !== undefined && result.error) {
        removeFullScreenLoader();
        console.error(result.error);
      } else if (result.cart_id !== undefined && result.cart_id) {
        // 2D flow success.
        const { cart } = this.props;
        placeOrder(cart.selected_payment_method);
        removeStorageInfo('spc_selected_card');
        removeStorageInfo('billing_shipping_same');
      } else if (result.success === undefined || !(result.success)) {
        // 3D flow error.
        console.error(result);
      } else if (result.redirectUrl !== undefined) {
        // 3D flow success.
        removeStorageInfo('spc_selected_card');
        removeStorageInfo('billing_shipping_same');
        window.location = result.redirectUrl;
      } else {
        console.error(result);
        removeFullScreenLoader();
      }
    }).catch((error) => {
      removeFullScreenLoader();
      console.error(error);
    });
  };

  render() {
    const { method } = this.props;
    const { isSelected, changePaymentMethod, cart } = this.props;

    return (
      <>
        <div className={`payment-method payment-method-${method.code}`} onClick={() => changePaymentMethod(method.code)}>
          <div className="payment-method-top-panel">
            <input
              id={`payment-method-${method.code}`}
              className={method.code}
              type="radio"
              defaultChecked={isSelected}
              value={method.code}
              name="payment-method"
            />

            <label className="radio-sim radio-label">
              {method.name}
              <ConditionalView condition={method.code === 'cashondelivery' && cart.cart.surcharge.amount > 0}>
                <CodSurchargePaymentMethodDescription surcharge={cart.cart.surcharge} />
              </ConditionalView>
            </label>

            <PaymentMethodIcon methodName={method.code} />
          </div>

          <ConditionalView condition={(isSelected && method.code === 'checkout_com')}>
            <div className={`payment-method-bottom-panel payment-method-form ${method.code}`}>
              <CheckoutComContextProvider>
                <PaymentMethodCheckoutCom
                  ref={this.paymentMethodCheckoutCom}
                  cart={cart}
                  finalisePayment={this.finalisePayment}
                />
              </CheckoutComContextProvider>
            </div>
          </ConditionalView>

          <ConditionalView condition={(isSelected && method.code === 'cybersource')}>
            <div className={`payment-method-bottom-panel payment-method-form ${method.code}`}>
              <PaymentMethodCybersource
                ref={this.paymentMethodCybersource}
                cart={cart}
                finalisePayment={this.finalisePayment}
              />
            </div>
          </ConditionalView>
        </div>
      </>
    );
  }
}
