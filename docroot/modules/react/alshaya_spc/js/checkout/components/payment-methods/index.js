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
  removeFullScreenLoader,
} from '../../../utilities/checkout_util';
import ConditionalView from '../../../common/components/conditional-view';
import dispatchCustomEvent from '../../../utilities/events';
import getStringMessage from '../../../utilities/strings';
import ApplePay from '../../../utilities/apple_pay';
import Postpay from '../../../utilities/postpay';
import PriceElement from '../../../utilities/special-price/PriceElement';
import isAuraEnabled, { isUserAuthenticated } from '../../../../../js/utilities/helper';
import {
  isFullPaymentDoneByAura,
  isPaymentMethodSetAsAura,
  isUnsupportedPaymentMethod,
} from '../../../aura-loyalty/components/utilities/checkout_helper';
import CheckoutComUpapiApplePay
  from '../../../utilities/checkout_com_upapi_apple_pay';
import Tabby from '../../../../../js/tabby/utilities/tabby';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';
import isEgiftCardEnabled from '../../../../../js/utilities/egiftCardHelper';
import PaymentMethodLinkedEgiftCard from '../../../egift-card/components/payment-method-linked-egift-card';
import { isEgiftRedemptionDone, isEgiftUnsupportedPaymentMethod, isFullPaymentDoneByEgift } from '../../../utilities/egift_util';

export default class PaymentMethods extends React.Component {
  constructor(props) {
    super(props);
    this.paymentMethodRefs = [];
    this.state = {
      postpayAvailable: [],
    };
  }

  componentDidMount = () => {
    this.selectDefault();

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
        // Adding Postpay identifier to the error message for the GA event.
        paymentErrorInfo.message = `Postpay: ${paymentErrorInfo.message}`;
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

    // We disable the other payment methods when full payment is done by aura points
    // and payment method is set as `aura_payment`.
    if (isAuraEnabled() && isPaymentMethodSetAsAura(cart)) {
      return false;
    }

    if (isDeliveryTypeSameAsInCart(cart)) {
      if (Postpay.isPostpayEnabled() && Postpay.isAvailable(this) == null) {
        return false;
      }
      return true;
    }
    return false;
  };

  selectDefault = () => {
    const { cart } = this.props;

    // If full payment is being done by aura then we change payment method to `aura_payment`.
    if (isAuraEnabled() && isFullPaymentDoneByAura(cart)) {
      this.changePaymentMethod('aura_payment');
      return;
    }

    // If full payment is being done by egift then change the payment method to
    // 'hps_payment'.
    if (isEgiftCardEnabled() && isFullPaymentDoneByEgift(cart.cart)) {
      // @todo To change payment method to hps_payment.
      // This will be done once we have default payment method selection disable
      // in place.
    }

    if (!(this.isActive())) {
      return;
    }

    const allPaymentMethods = this.getPaymentMethods(true);
    const { postpayAvailable } = this.state;

    // Prepare object containing only the methods available for use.
    const paymentMethods = {};
    Object.keys(allPaymentMethods).forEach((key) => {
      // If status is set and disabled, do not use the method.
      if (hasValue(allPaymentMethods[key].status) && allPaymentMethods[key].status === 'disabled') {
        return;
      }

      // If postpay is not available remove from available methods list.
      if (key === 'postpay' && !postpayAvailable[cart.cart.cart_total]) {
        return;
      }

      paymentMethods[key] = allPaymentMethods[key];
    });

    if (Object.keys(paymentMethods).length === 0) {
      return;
    }

    const paymentDiv = document.getElementById(`payment-method-${cart.cart.payment.method}`);

    if (cart.cart.payment.method === undefined
      || paymentMethods[cart.cart.payment.method] === undefined
      || paymentDiv === null
      || paymentDiv.checked !== true) {
      // Select previously selected method if available.
      if (cart.cart.payment.method !== undefined
        && cart.cart.payment.method !== null
        && paymentMethods[cart.cart.payment.method] !== undefined) {
        this.changePaymentMethod(cart.cart.payment.method);
        return;
      }

      // Select default from previous order if available.
      if (cart.cart.payment.default !== undefined
        && cart.cart.payment.default !== null
        && paymentMethods[cart.cart.payment.default] !== undefined) {
        this.changePaymentMethod(cart.cart.payment.default);
        return;
      }

      // Select first payment method by default.
      const sortedMethods = Object.values(paymentMethods).sort((a, b) => a.weight - b.weight);
      this.changePaymentMethod(sortedMethods[0].code);
    }
  };

  getPaymentMethods = (active) => {
    const { cart } = this.props;

    const paymentMethods = [];

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

          if (method.code === 'postpay' && !Postpay.isAvailable(this)) {
            return;
          }

          if (method.code === 'tabby' && !Tabby.isAvailable()) {
            return;
          }

          paymentMethods[method.code] = drupalSettings.payment_methods[method.code];
          // Get the product available for tabby.
          if (method.code === 'tabby') {
            const available = Tabby.productAvailable(this);
            paymentMethods[method.code].status = available.status;
            if (hasValue(available.rejection_reason)) {
              paymentMethods[method.code].rejection_reason = available.rejection_reason;
            }
          }
        }
      });
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

    // If aura enabled and aura points redeemed then do not allow
    // to select any payment method that is unsupported with aura.
    if (isAuraEnabled()
      && cart.cart.totals.paidWithAura > 0
      && isUnsupportedPaymentMethod(method)) {
      return;
    }

    // Allow change payment method only if it's allowed for egift.
    if (isEgiftCardEnabled()
      && isEgiftUnsupportedPaymentMethod(method)
      && isEgiftRedemptionDone(cart.cart, cart.cart.totals.egiftRedemptionType)) {
      return;
    }

    // If method is already selected in cart we simply
    // trigger the events.
    if (method && cart.cart.payment.method === method) {
      this.processPostPaymentSelection(method);
      return;
    }

    showFullScreenLoader();

    const analytics = Drupal.alshayaSpc.getGAData();

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
          // Close popup in case of error.
          removeFullScreenLoader();
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
    let disablePaymentMethod = '';

    const active = this.isActive();
    const { cart, refreshCart } = this.props;
    const activePaymentMethods = Object.values(this.getPaymentMethods(active))
      .sort((a, b) => a.weight - b.weight);
    const animationInterval = 0.4 / Object.keys(activePaymentMethods).length;

    Object.entries(activePaymentMethods).forEach(([, method], index) => {
      // If aura enabled and customer is paying some amount of the order
      // using aura points then disable the payment methods that are
      // not supported with Aura.
      if (isAuraEnabled() && cart.cart.totals.paidWithAura > 0) {
        disablePaymentMethod = isUnsupportedPaymentMethod(method.code);
      }

      // Check if egift card is already redeemed with linked or guest.
      const egiftRedeemed = isEgiftRedemptionDone(cart.cart, cart.cart.totals.egiftRedemptionType);

      // Disable the payment method that are not supported by egift.
      if (isEgiftCardEnabled() && egiftRedeemed) {
        disablePaymentMethod = isEgiftUnsupportedPaymentMethod(method.code);
      }

      this.paymentMethodRefs[method.code] = React.createRef();
      const animationOffset = animationInterval * index;
      methods.push(<PaymentMethod
        cart={cart}
        ref={this.paymentMethodRefs[method.code]}
        refreshCart={refreshCart}
        changePaymentMethod={this.changePaymentMethod}
        isSelected={cart.cart.payment.method === method.code}
        key={method.code}
        method={method}
        animationOffset={animationOffset}
        {...((isAuraEnabled() || (isEgiftCardEnabled() && egiftRedeemed))
          && disablePaymentMethod
          && { disablePaymentMethod }
        )}
      />);
    });

    const activeClass = active ? 'active' : 'in-active';

    return (
      <div id="spc-payment-methods" className={`spc-checkout-payment-options fadeInUp ${activeClass}`} style={{ animationDelay: '0.4s' }}>
        <SectionTitle>{Drupal.t('Payment Methods')}</SectionTitle>
        <ConditionalView condition={isEgiftCardEnabled() && isUserAuthenticated()}>
          <PaymentMethodLinkedEgiftCard
            cart={cart}
            egiftGuestRedeemed={isEgiftRedemptionDone(cart.cart)}
          />
        </ConditionalView>
        <ConditionalView condition={Object.keys(methods).length > 0}>
          <div className={`payment-methods ${activeClass}`}>{methods}</div>
        </ConditionalView>
      </div>
    );
  }
}
