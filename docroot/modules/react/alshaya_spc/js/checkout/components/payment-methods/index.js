import React from 'react';
import { renderToString } from 'react-dom/server';
import Cookies from 'js-cookie';
import parse from 'html-react-parser';
import SectionTitle from '../../../utilities/section-title';
import PaymentMethod from '../payment-method';
import { addPaymentMethodInCart } from '../../../utilities/update_cart';
import {
  isDeliveryTypeSameAsInCart,
  showFullScreenLoader,
} from '../../../utilities/checkout_util';
import ConditionalView from '../../../common/components/conditional-view';
import dispatchCustomEvent from '../../../utilities/events';
import getStringMessage from '../../../utilities/strings';
import ApplePay from '../../../utilities/apple_pay';
import PriceElement from '../../../utilities/special-price/PriceElement';
import CheckoutComUpapiApplePay
  from '../../../utilities/checkout_com_upapi_apple_pay';
import isPostpayEnabled from '../../../utilities/helper';

export default class PaymentMethods extends React.Component {
  constructor(props) {
    super(props);
    this.paymentMethodRefs = [];
    this.state = {
      postpayAvailable: false,
    };
  }

  componentDidMount = () => {
    const { cart } = this.props;

    if (isPostpayEnabled()) {
      const postpayTimer = setInterval(() => {
        const { isPostpayInitialised } = this.props;
        if (isPostpayInitialised) {
          window.postpay.check_amount({
            amount: cart.cart.cart_total * drupalSettings.postpay.currency_multiplier,
            currency: drupalSettings.postpay_widget_info['data-currency'],
            callback: function (paymentOptions) {
              if (paymentOptions === null) {
                // Hide Postpay payment method if the payment_options is
                // not available.
                document.getElementById('.payment-method-postpay').style.display = 'none';
              } else {
                this.setState({ postpayAvailable: true });
              }
            }.bind(this),
          });
          this.selectDefault();
          clearInterval(postpayTimer);
        }
      }, 100);
    } else {
      this.selectDefault();
    }

    // We want this to be executed once all other JS execution is finished.
    // For this we use setTimeout with 1 ms.
    setTimeout(() => {
      dispatchCustomEvent('refreshCompletePurchaseSection', {});
    }, 1);

    const paymentError = Cookies.get('middleware_payment_error');
    if (paymentError !== undefined && paymentError !== null && paymentError.length > 0) {
      Cookies.remove('middleware_payment_error');

      const paymentErrorInfo = JSON.parse(paymentError);
      let message = getStringMessage('payment_error');


      if (paymentErrorInfo.status === 'declined' && paymentErrorInfo.payment_method === 'postpay') {
        message = parse(getStringMessage('postpay_error'));
      } else if (paymentErrorInfo.status !== undefined && paymentErrorInfo.status === 'place_order_failed') {
        // If K-NET error and have K-Net Error details.
        const errorData = {};
        Object.entries(paymentErrorInfo.data).forEach(([key, value]) => {
          errorData[`@${key}`] = value;
        });

        const transactionData = getStringMessage(`${paymentErrorInfo.payment_method}_error_info`, errorData);
        message = parse(getStringMessage('place_order_failed_error', {
          '@transaction_data': transactionData,
        }));
      } else if (paymentErrorInfo.payment_method !== undefined
        && paymentErrorInfo.payment_method === 'knet'
        && paymentErrorInfo.data !== undefined) {
        message = parse(getStringMessage('knet_error', {
          '@transaction_id': paymentErrorInfo.data.transaction_id,
          '@payment_id': paymentErrorInfo.data.payment_id,
          '@result_code': paymentErrorInfo.data.result_code,
        }));
      } else if (paymentErrorInfo.status !== undefined
        && paymentErrorInfo.status === 'declined') {
        message = getStringMessage('transaction_failed');

        if (paymentErrorInfo.data !== undefined) {
          const errorData = {};
          Object.entries(paymentErrorInfo.data).forEach(([key, value]) => {
            errorData[`@${key}`] = (key === 'amount')
              ? renderToString(<PriceElement amount={value} format="string" />)
              : value;
          });

          const transactionData = getStringMessage(`${paymentErrorInfo.payment_method}_error_info`, errorData);
          message = parse(`${message}<br/>${transactionData}`);
        }
      }

      // Push error to GA.
      Drupal.logJavascriptError(
        'payment-error',
        paymentErrorInfo,
        GTM_CONSTANTS.GENUINE_PAYMENT_ERRORS,
      );

      dispatchCustomEvent('spcCheckoutMessageUpdate', {
        type: 'error',
        message,
      });
    }
  };

  componentDidUpdate() {
    this.selectDefault();
    dispatchCustomEvent('refreshCompletePurchaseSection', {});
  }

  isActive = () => {
    const { cart } = this.props;

    if (cart.cart.payment.methods === undefined || cart.cart.payment.methods.length === 0) {
      return false;
    }

    return isDeliveryTypeSameAsInCart(cart);
  };

  selectDefault = () => {
    if (!(this.isActive())) {
      return;
    }

    const paymentMethods = this.getPaymentMethods(true);

    if (Object.keys(paymentMethods).length === 0) {
      return;
    }

    const { postpayAvailable } = this.state;
    const { cart } = this.props;

    const paymentDiv = document.getElementById(`payment-method-${cart.cart.payment.method}`);
    if (cart.cart.payment.method === undefined
      || paymentMethods[cart.cart.payment.method] === undefined
      || paymentDiv === null
      || paymentDiv.checked !== true) {
      // Select previously selected method if available.
      if (cart.cart.payment.method !== undefined
        && cart.cart.payment.method !== null
        && paymentMethods[cart.cart.payment.method] !== undefined) {
        if (postpayAvailable || cart.cart.payment.method !== 'postpay') {
          this.changePaymentMethod(cart.cart.payment.method);
          return;
        }
      }

      // Select default from previous order if available.
      if (cart.cart.payment.default !== undefined
        && cart.cart.payment.default !== null
        && paymentMethods[cart.cart.payment.default] !== undefined) {
        if (postpayAvailable || cart.cart.payment.default !== 'postpay') {
          this.changePaymentMethod(cart.cart.payment.default);
          return;
        }
      }

      // Select first payment method by default.
      if (postpayAvailable || Object.keys(paymentMethods)[0] !== 'postpay') {
        this.changePaymentMethod(Object.keys(paymentMethods)[0]);
      } else {
        this.changePaymentMethod(Object.keys(paymentMethods)[1]);
      }
    }
  };

  getPaymentMethods = (active) => {
    const { cart } = this.props;
    let paymentMethods = [];

    if (active) {
      Object.entries(cart.cart.payment.methods).forEach(([, method]) => {
        // If payment method from api response not exists in
        // available payment methods in drupal, ignore it.
        if (method.code in drupalSettings.payment_methods) {
          if (method.code === 'checkout_com_applepay' && !(ApplePay.isAvailable())) {
            return;
          }

          if (method.code === 'checkout_com_upapi_applepay' && !(CheckoutComUpapiApplePay.isAvailable())) {
            return;
          }

          paymentMethods[method.code] = drupalSettings.payment_methods[method.code];
        }
      });

      paymentMethods = paymentMethods.sort((a, b) => a.weight - b.weight);
    } else {
      Object.entries(drupalSettings.payment_methods).forEach(([, method]) => {
        if (!(cart.delivery_type !== undefined && cart.delivery_type === 'click_and_collect' && method.code === 'cashondelivery')) {
          paymentMethods[method.code] = drupalSettings.payment_methods[method.code];
        }
      });
    }

    return paymentMethods;
  };

  processPostPaymentSelection = (method) => {
    const paymentDiv = document.getElementById(`payment-method-${method}`);
    if (paymentDiv === null) {
      return;
    }

    const { cart: cartData } = this.props;

    const methodIdentifer = `${method}:${cartData.cart.cart_id}`;

    // If we have already triggered once for the method and cart do nothing.
    const lastSelectedMethodIdentifier = localStorage.getItem('last_selected_payment');
    if (paymentDiv.checked && lastSelectedMethodIdentifier === methodIdentifer) {
      return;
    }

    localStorage.setItem('last_selected_payment', methodIdentifer);

    paymentDiv.checked = true;

    // Dispatch event for GTM checkout step 3.
    dispatchCustomEvent('refreshCartOnPaymentMethod', {
      cart: cartData.cart,
    });

    dispatchCustomEvent('refreshCompletePurchaseSection', {});
  };

  changePaymentMethod = (method) => {
    const { cart, refreshCart } = this.props;

    if (!this.isActive()) {
      return;
    }

    // If method is already selected in cart we simply
    // trigger the events.
    if (method && cart.cart.payment.method === method) {
      this.processPostPaymentSelection(method);
      return;
    }

    showFullScreenLoader();

    const analytics = {};
    if (typeof window.ga === 'function' && window.ga.loaded) {
      analytics.clientId = window.ga.getAll()[0].get('clientId');
      analytics.trackingId = window.ga.getAll()[0].get('trackingId');
    }

    const data = {
      payment: {
        method,
        additional_data: {},
        analytics,
      },
    };

    const cartUpdate = addPaymentMethodInCart('update payment', data);
    if (cartUpdate instanceof Promise) {
      cartUpdate.then((result) => {
        if (!result) {
          return;
        }
        const paymentDiv = document.getElementById(`payment-method-${method}`);
        if (paymentDiv === null) {
          this.selectDefault();
          return;
        }

        const { cart: cartData } = this.props;
        cartData.cart = result;
        refreshCart(cartData);

        this.processPostPaymentSelection(method);
      }).catch((error) => {
        Drupal.logJavascriptError('change payment method', error, GTM_CONSTANTS.GENUINE_PAYMENT_ERRORS);
      });
    }
  };

  validateBeforePlaceOrder = () => {
    const { cart } = this.props;

    // Trigger validate of selected component.
    return this.paymentMethodRefs[cart.cart.payment.method].current.validateBeforePlaceOrder();
  };

  render = () => {
    const methods = [];

    const active = this.isActive();
    const { cart, refreshCart } = this.props;
    const activePaymentMethods = this.getPaymentMethods(active);
    const animationInterval = 0.4 / Object.keys(activePaymentMethods).length;

    Object.entries(activePaymentMethods).forEach(([key, method], index) => {
      this.paymentMethodRefs[method.code] = React.createRef();
      const animationOffset = animationInterval * index;
      methods.push(<PaymentMethod
        cart={cart}
        ref={this.paymentMethodRefs[method.code]}
        refreshCart={refreshCart}
        changePaymentMethod={this.changePaymentMethod}
        isSelected={cart.cart.payment.method === method.code}
        key={key}
        method={method}
        animationOffset={animationOffset}
      />);
    });

    const activeClass = active ? 'active' : 'in-active';

    return (
      <div id="spc-payment-methods" className={`spc-checkout-payment-options fadeInUp ${activeClass}`} style={{ animationDelay: '0.4s' }}>
        <ConditionalView condition={Object.keys(methods).length > 0}>
          <SectionTitle>{Drupal.t('Payment Methods')}</SectionTitle>
          <div className={`payment-methods ${activeClass}`}>{methods}</div>
        </ConditionalView>
      </div>
    );
  }
}
