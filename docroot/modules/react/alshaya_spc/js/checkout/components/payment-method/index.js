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
import PaymentMethodPostpay from '../payment-method-postpay';
import dispatchCustomEvent from '../../../utilities/events';
import getStringMessage from '../../../utilities/strings';
import CheckoutComUpapiContextProvider from '../../../context/CheckoutComUpapi';
import PaymentMethodCheckoutComUpapi from '../payment-method-checkout-com-upapi';

export default class PaymentMethod extends React.Component {
  constructor(props) {
    super(props);

    this.paymentMethodCheckoutCom = React.createRef();
    this.paymentMethodCheckoutComUpapi = React.createRef();
    this.paymentMethodApplePay = React.createRef();
    this.paymentMethodPostpay = React.createRef();
    this.paymentMethodCybersource = React.createRef();
  }

  validateBeforePlaceOrder = () => {
    const { method } = this.props;
    // Do additional process for some payment methods.
    if (method.code === 'checkout_com') {
      return this.paymentMethodCheckoutCom.current.validateBeforePlaceOrder();
    }

    if (method.code === 'checkout_com_upapi') {
      return this.paymentMethodCheckoutComUpapi.current.validateBeforePlaceOrder();
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
      if (!result) {
        return;
      }
      if (result.error !== undefined && result.error) {
        removeFullScreenLoader();
        if (result.error_code !== undefined) {
          const errorCode = parseInt(result.error_code, 10);
          if (errorCode === 505) {
            Drupal.logJavascriptError('finalise payment', result.error_message, GTM_CONSTANTS.CHECKOUT_ERRORS);

            dispatchCustomEvent('spcCheckoutMessageUpdate', {
              type: 'error',
              message: getStringMessage('shipping_method_error'),
            });
          } else if (errorCode === 500 && result.error_message !== undefined) {
            Drupal.logJavascriptError('finalise payment', result.error_message, GTM_CONSTANTS.PAYMENT_ERRORS);

            dispatchCustomEvent('spcCheckoutMessageUpdate', {
              type: 'error',
              message: result.error_message,
            });
          } else if (errorCode === 404) {
            // Cart no longer available, redirect user to basket.
            Drupal.logJavascriptError('finalise payment', result.error_message, GTM_CONSTANTS.CHECKOUT_ERRORS);
            window.location = Drupal.url('cart');
          } else {
            const errorMessage = result.message === undefined
              ? result.error_message
              : result.message;

            Drupal.logJavascriptError('finalise payment', errorMessage, GTM_CONSTANTS.GENUINE_PAYMENT_ERRORS);
          }
        }
      } else if (result.cart_id !== undefined && result.cart_id) {
        // 2D flow success.
        const { cart } = this.props;
        placeOrder(cart.cart.payment.method);
        removeStorageInfo('spc_selected_card');
        removeStorageInfo('billing_shipping_same');
      } else if (result.success === undefined || !(result.success)) {
        // 3D flow error.
        Drupal.logJavascriptError('3d flow finalise payment', result.message, GTM_CONSTANTS.GENUINE_PAYMENT_ERRORS);
      } else if (result.redirectUrl !== undefined) {
        // 3D flow success.
        removeStorageInfo('spc_selected_card');
        removeStorageInfo('billing_shipping_same');
        window.location = result.redirectUrl;
      } else {
        Drupal.logJavascriptError('finalise payment', result.message, GTM_CONSTANTS.GENUINE_PAYMENT_ERRORS);
        removeFullScreenLoader();
      }
    }).catch((error) => {
      removeFullScreenLoader();
      Drupal.logJavascriptError('add payment method in cart', error, GTM_CONSTANTS.GENUINE_PAYMENT_ERRORS);
    });
  };

  render() {
    const { method } = this.props;
    const {
      isSelected,
      changePaymentMethod,
      cart,
      animationOffset,
    } = this.props;
    const animationDelayValue = `${0.4 + animationOffset}s`;

    if (method.code === 'checkout_com_applepay') {
      if (!(ApplePay.isAvailable())) {
        return (null);
      }
    }

    return (
      <>
        <div className={`payment-method fadeInUp payment-method-${method.code}`} style={{ animationDelay: animationDelayValue }} onClick={() => changePaymentMethod(method.code)}>
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

          <ConditionalView condition={(isSelected && method.code === 'checkout_com_upapi')}>
            <div className={`payment-method-bottom-panel payment-method-form ${method.code}`}>
              <CheckoutComUpapiContextProvider>
                <PaymentMethodCheckoutComUpapi
                  ref={this.paymentMethodCheckoutComUpapi}
                  cart={cart}
                  finalisePayment={this.finalisePayment}
                />
              </CheckoutComUpapiContextProvider>
            </div>
          </ConditionalView>

          <ConditionalView condition={(isSelected && method.code === 'postpay')}>
            <div className={`payment-method-bottom-panel payment-method-form ${method.code}`}>
              <PaymentMethodPostpay
                ref={this.PaymentMethodPostpay}
                postpay={drupalSettings.postpay}
                postpayWidgetInfo={drupalSettings.postpay_widget_info}
                cart={cart}
                finalisePayment={this.finalisePayment}
              />
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
