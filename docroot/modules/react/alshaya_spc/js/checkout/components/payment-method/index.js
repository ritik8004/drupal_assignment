import React from 'react';
import ConditionalView from '../../../common/components/conditional-view';
import CodSurchargeInformation from '../payment-description-cod-surchage';
import PaymentMethodCheckoutCom from '../payment-method-checkout-com';
import PaymentMethodIcon from '../../../svg-component/payment-method-svg';
import { addPaymentMethodInCart } from '../../../utilities/update_cart';
import {
  placeOrder,
  removeFullScreenLoader,
  setUpapiApplePayCofig,
} from '../../../utilities/checkout_util';
import CheckoutComContextProvider from '../../../context/CheckoutCom';
import { removeStorageInfo } from '../../../utilities/storage';
import PaymentMethodApplePay from '../payment-method-apple-pay';
import ApplePay from '../../../utilities/apple_pay';
import PaymentMethodPostpay from '../payment-method-postpay';
import dispatchCustomEvent from '../../../utilities/events';
import getStringMessage from '../../../utilities/strings';
import CheckoutComUpapiContextProvider from '../../../context/CheckoutComUpapi';
import PaymentMethodCheckoutComUpapi from '../payment-method-checkout-com-upapi';
import PaymentMethodCheckoutComUpapiApplePay from '../payment-method-checkout-com-upapi-apple-pay';
import CheckoutComUpapiApplePay
  from '../../../utilities/checkout_com_upapi_apple_pay';
import PaymentMethodCheckoutComUpapiFawry
  from '../payment-method-checkout-com-upapi-fawry';
import cartActions from '../../../utilities/cart_actions';
import { isFullPaymentDoneByAura } from '../../../aura-loyalty/components/utilities/checkout_helper';
import isAuraEnabled from '../../../../../js/utilities/helper';
import PaymentMethodTabby from '../payment-method-tabby';
import TabbyWidget from '../../../../../js/tabby/components';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';

export default class PaymentMethod extends React.Component {
  constructor(props) {
    super(props);

    this.paymentMethodCheckoutCom = React.createRef();
    this.paymentMethodCheckoutComUpapi = React.createRef();
    this.paymentMethodApplePay = React.createRef();
    this.paymentMethodPostpay = React.createRef();
    this.paymentMethodTabby = React.createRef();
    this.paymentMethodCheckoutComUpapiApplePay = React.createRef();
  }

  componentDidMount() {
    setUpapiApplePayCofig();
  }

  validateBeforePlaceOrder = async () => {
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

    if (method.code === 'checkout_com_upapi_applepay') {
      return this.paymentMethodCheckoutComUpapiApplePay.current.validateBeforePlaceOrder();
    }

    // Now update the payment method data in the cart.
    // This is done so that if the site has switched from V1 to V2 for the
    // commer backend and if a cart has payment method like checkoutcom KNET
    // set, then the redirect url is set to the V1 middleware route, which is
    // incorrect. So we update the payment method so that the proper V2 redirect
    // URL is set.
    const analytics = Drupal.alshayaSpc.getGAData();
    const data = {
      payment: {
        method: method.code,
        additional_data: {},
        analytics,
      },
    };
    await addPaymentMethodInCart('update payment', data);

    return true;
  };

  finalisePayment = (paymentData) => {
    addPaymentMethodInCart(cartActions.cartPaymentFinalise, paymentData).then((result) => {
      if (!result) {
        // If validation fails, addPaymentMethodInCart(), returns null.
        removeFullScreenLoader();
        return;
      }
      if (result.error !== undefined && result.error) {
        removeFullScreenLoader();
        if (result.error_code !== undefined) {
          const errorCode = parseInt(result.error_code, 10);
          if (errorCode === 505) {
            Drupal.logJavascriptError(
              cartActions.cartPaymentFinalise,
              result.error_message,
              GTM_CONSTANTS.CHECKOUT_ERRORS,
            );

            dispatchCustomEvent('spcCheckoutMessageUpdate', {
              type: 'error',
              message: getStringMessage('shipping_method_error'),
            });
          } else if (errorCode === 500 && result.error_message !== undefined) {
            Drupal.logJavascriptError(
              cartActions.cartPaymentFinalise,
              result.error_message,
              GTM_CONSTANTS.PAYMENT_ERRORS,
            );

            dispatchCustomEvent('spcCheckoutMessageUpdate', {
              type: 'error',
              message: result.error_message,
            });
          } else if (errorCode === 404) {
            // Cart no longer available, redirect user to basket.
            Drupal.logJavascriptError(
              cartActions.cartPaymentFinalise,
              result.error_message,
              GTM_CONSTANTS.CHECKOUT_ERRORS,
            );
            window.location = Drupal.url('cart');
          } else {
            const errorMessage = result.message === undefined
              ? result.error_message
              : result.message;

            Drupal.logJavascriptError(
              cartActions.cartPaymentFinalise,
              errorMessage,
              GTM_CONSTANTS.GENUINE_PAYMENT_ERRORS,
            );
          }

          // Enable the 'place order' CTA.
          dispatchCustomEvent('updatePlaceOrderCTA', {
            status: true,
          });
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
        Drupal.logJavascriptError(
          cartActions.cartPaymentFinalise,
          result.message,
          GTM_CONSTANTS.GENUINE_PAYMENT_ERRORS,
        );
        removeFullScreenLoader();
      }
    }).catch((error) => {
      removeFullScreenLoader();
      Drupal.logJavascriptError('add payment method in cart', error, GTM_CONSTANTS.GENUINE_PAYMENT_ERRORS);
    });
  };

  render() {
    const {
      method,
      isSelected,
      changePaymentMethod,
      cart,
      animationOffset,
      disablePaymentMethod,
    } = this.props;
    const animationDelayValue = `${0.4 + animationOffset}s`;

    if (method.code === 'checkout_com_applepay' && !(ApplePay.isAvailable())) {
      return (null);
    }

    if (method.code === 'checkout_com_upapi_applepay' && !(CheckoutComUpapiApplePay.isAvailable())) {
      return (null);
    }
    let additionalClasses = '';
    let methodNameSuffix = '';

    // Hide by default if AB Testing is enabled and method not selected already.
    if (method.ab_testing && !(isSelected)) {
      additionalClasses = 'ab-testing-hidden';
    }

    // @todo make this work with generic way added now above.
    if (method.code === 'postpay') {
      additionalClasses = drupalSettings.postpay_widget_info.postpay_mode_class;
    }

    // Add `in-active` class if disablePaymentMethod property is true.
    additionalClasses = disablePaymentMethod
      ? `${additionalClasses} in-active`
      : additionalClasses;

    // Set addition class for tabby.
    if (method.code === 'tabby' && hasValue(method.status)) {
      additionalClasses = method.status;
      if (method.status === 'disabled') {
        methodNameSuffix = method.rejection_reason;
      }
    }

    return (
      <>
        <div className={`payment-method fadeInUp payment-method-${method.code} ${additionalClasses}`} style={{ animationDelay: animationDelayValue }} onClick={() => changePaymentMethod(method.code)}>
          <div className="payment-method-top-panel">
            <input
              id={`payment-method-${method.code}`}
              className={method.code}
              type="radio"
              defaultChecked={isSelected}
              value={method.code}
              name="payment-method"
              {...(disablePaymentMethod
                && { disabled: disablePaymentMethod })}
            />

            <div className="payment-method-label-wrapper">
              <label className="radio-sim radio-label">
                {method.name}
                <ConditionalView condition={methodNameSuffix !== ''}>
                  <span className="payment-method-name-suffix">{methodNameSuffix}</span>
                </ConditionalView>
                <ConditionalView condition={method.code === 'cashondelivery' && typeof (cart.cart.surcharge) !== 'undefined' && cart.cart.surcharge.amount > 0}>
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

              <ConditionalView condition={method.code === 'tabby'}>
                <TabbyWidget
                  pageType="checkout"
                  classNames="installment-popup"
                />
              </ConditionalView>
            </div>
            <PaymentMethodIcon methodName={method.code} methodLabel={method.name} />
          </div>
          <ConditionalView condition={isAuraEnabled()
          && method.code === 'cashondelivery'
          && disablePaymentMethod === true
          && !isFullPaymentDoneByAura(cart)}
          >
            <div className="payment-method-bottom-panel no-payment-description">
              {Drupal.t('Cash on delivery is not available along with the Aura points.')}
            </div>
          </ConditionalView>

          <ConditionalView condition={isSelected && method.code === 'cashondelivery' && typeof (cart.cart.surcharge) !== 'undefined' && cart.cart.surcharge.amount > 0}>
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
              />
            </div>
          </ConditionalView>

          <ConditionalView condition={(isSelected && method.code === 'tabby' && method.status === 'enabled')}>
            <div className={`payment-method-bottom-panel payment-method-form ${method.code}`}>
              <PaymentMethodTabby
                ref={this.paymentMethodTabby}
                tabby={drupalSettings.tabby}
                cart={cart}
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

          <ConditionalView condition={isSelected && method.code === 'checkout_com_upapi_applepay'}>
            <PaymentMethodCheckoutComUpapiApplePay
              ref={this.paymentMethodCheckoutComUpapiApplePay}
              cart={cart}
              finalisePayment={this.finalisePayment}
            />
          </ConditionalView>

          <ConditionalView condition={isSelected && method.code === 'checkout_com_upapi_fawry'}>
            <div className={`payment-method-bottom-panel payment-method-form ${method.code}`}>
              <PaymentMethodCheckoutComUpapiFawry
                cart={cart}
              />
            </div>
          </ConditionalView>
        </div>
      </>
    );
  }
}
