import React from 'react';
import ConditionalView from '../../../common/components/conditional-view';
import CodSurchargeInformation from '../payment-description-cod-surchage';
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
import PaymentMethodApplePay from '../payment-method-apple-pay';
import ApplePay from '../../../utilities/apple_pay';

export default class PaymentMethod extends React.Component {
  constructor(props) {
    super(props);

    this.paymentMethodCheckoutCom = React.createRef();
    this.paymentMethodApplePay = React.createRef();
    this.paymentMethodCybersource = React.createRef();
  }

  validateBeforePlaceOrder = () => {
    const { method } = this.props;
    // Do additional process for some payment methods.
    if (method.code === 'checkout_com') {
      return this.paymentMethodCheckoutCom.current.validateBeforePlaceOrder();
    }

    if (method.code === 'checkout_com_applepay') {
      return this.paymentMethodApplePay.current.validateBeforePlaceOrder();
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
        Drupal.logJavascriptError('finalise payment', result.message);
      } else if (result.cart_id !== undefined && result.cart_id) {
        // 2D flow success.
        const { cart } = this.props;
        placeOrder(cart.cart.cart_payment_method);
        removeStorageInfo('spc_selected_card');
        removeStorageInfo('billing_shipping_same');
      } else if (result.success === undefined || !(result.success)) {
        // 3D flow error.
        Drupal.logJavascriptError('3d flow finalise payment', result.message);
      } else if (result.redirectUrl !== undefined) {
        // 3D flow success.
        removeStorageInfo('spc_selected_card');
        removeStorageInfo('billing_shipping_same');
        window.location = result.redirectUrl;
      } else {
        Drupal.logJavascriptError('finalise payment', result.message);
        removeFullScreenLoader();
      }
    }).catch((error) => {
      removeFullScreenLoader();
      Drupal.logJavascriptError('add payment method in cart', error);
    });
  };

  render() {
    const { method } = this.props;
    const { isSelected, changePaymentMethod, cart } = this.props;

    if (method.code === 'checkout_com_applepay') {
      if (!(ApplePay.isAvailable())) {
        return (null);
      }
    }

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
                <div className="spc-payment-method-desc">
                  <div className="desc-content">
                    <CodSurchargeInformation
                      surcharge={cart.cart.surcharge}
                      messageKey="cod_surcharge_short_description"
                    />
                  </div>
                </div>
              </ConditionalView>
            </label>

            <PaymentMethodIcon methodName={method.code} />
          </div>

          <ConditionalView condition={isSelected && method.code === 'cashondelivery' && cart.cart.surcharge.amount > 0}>
            <div className={`payment-method-bottom-panel ${method.code}`}>
              <div className="cod-surcharge-desc">
                <CodSurchargeInformation
                  surcharge={cart.cart.surcharge}
                  messageKey="cod_surcharge_description"
                />
              </div>
            </div>
          </ConditionalView>

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

          <ConditionalView condition={isSelected && method.code === 'checkout_com_applepay'}>
            <PaymentMethodApplePay
              ref={this.paymentMethodApplePay}
              cart={cart}
              finalisePayment={this.finalisePayment}
            />
          </ConditionalView>
        </div>
      </>
    );
  }
}
