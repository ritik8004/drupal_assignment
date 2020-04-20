import React from 'react';
import Cookies from 'js-cookie';
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

export default class PaymentMethods extends React.Component {
  constructor(props) {
    super(props);
    this.paymentMethodRefs = [];
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

      const message = (paymentError === 'declined')
        ? getStringMessage('transaction_failed')
        : getStringMessage('payment_error');

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

    if (cart.cart.payment_methods === undefined) {
      return false;
    }

    if (cart.cart.payment_methods.length === 0) {
      return false;
    }

    return isDeliveryTypeSameAsInCart(cart);
  };

  selectDefault = () => {
    if (!(this.isActive())) {
      return;
    }

    const paymentMethods = this.getPaymentMethods(true);
    const { cart } = this.props;

    // Select first payment method by default.
    if (cart.cart.cart_payment_method === undefined
      || paymentMethods[cart.cart.cart_payment_method] === undefined) {
      this.changePaymentMethod(Object.keys(paymentMethods)[0]);
    }
  };

  getPaymentMethods = (active) => {
    const { cart } = this.props;
    let paymentMethods = [];

    if (active) {
      Object.entries(cart.cart.payment_methods).forEach(([, method]) => {
        // If payment method from api response not exists in
        // available payment methods in drupal, ignore it.
        if (method.code in drupalSettings.payment_methods) {
          paymentMethods[method.code] = drupalSettings.payment_methods[method.code];
        }
      });

      paymentMethods = paymentMethods.sort((a, b) => a.weight - b.weight);
    } else {
      Object.entries(drupalSettings.payment_methods).forEach(([, method]) => {
        if (!(cart.delivery_type !== undefined && cart.delivery_type === 'cnc' && method.code === 'cashondelivery')) {
          paymentMethods[method.code] = drupalSettings.payment_methods[method.code];
        }
      });
    }

    return paymentMethods;
  };

  changePaymentMethod = (method) => {
    const { cart, refreshCart } = this.props;

    // Dispatch event for GTM checkout step 3.
    dispatchCustomEvent('refreshCartOnPaymentMethod', {
      cart,
    });

    if (!this.isActive() || cart.cart.cart_payment_method === method) {
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
        // @TODO: Handle exception.
        document.getElementById(`payment-method-${method}`).checked = true;

        const { cart: cartData } = this.props;
        cartData.cart = result;
        refreshCart(cartData);

        dispatchCustomEvent('refreshCompletePurchaseSection', {});
      }).catch((error) => {
        Drupal.logJavascriptError('change payment method', error);
      });
    }
  };

  validateBeforePlaceOrder = () => {
    const { cart } = this.props;

    // Trigger validate of selected component.
    return this.paymentMethodRefs[cart.cart.cart_payment_method].current.validateBeforePlaceOrder();
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
        isSelected={cart.cart.cart_payment_method === method.code}
        key={key}
        method={method}
        animationOffset={animationOffset}
      />);
    });

    const activeClass = active ? 'active' : 'in-active';

    return (
      <div id="spc-payment-methods" className="spc-checkout-payment-options fadeInUp" style={{ animationDelay: '0.4s' }}>
        <ConditionalView condition={Object.keys(methods).length > 0}>
          <SectionTitle>{Drupal.t('Payment Methods')}</SectionTitle>
          <div className={`payment-methods ${activeClass}`}>{methods}</div>
        </ConditionalView>
      </div>
    );
  }
}
