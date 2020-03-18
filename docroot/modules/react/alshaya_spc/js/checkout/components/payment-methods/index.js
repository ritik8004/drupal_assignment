import React from 'react';

import SectionTitle from '../../../utilities/section-title';
import PaymentMethod from '../payment-method';
import { addPaymentMethodInCart } from '../../../utilities/update_cart';
import {
  addInfoInStorage
} from '../../../utilities/storage';
import {
  isDeliveryTypeSameAsInCart,
  showFullScreenLoader,
} from '../../../utilities/checkout_util';
import ConditionalView from '../../../common/components/conditional-view';

export default class PaymentMethods extends React.Component {
  constructor(props) {
    super(props);
    this.paymentMethodRefs = [];
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
    if (cart.selected_payment_method === 'undefined' || paymentMethods[cart.selected_payment_method] === undefined) {
      this.changePaymentMethod(Object.keys(paymentMethods)[0]);
    }
  };

  componentDidMount = () => {
    this.selectDefault();
  };

  componentDidUpdate(prevProps, prevState, snapshot) {
    this.selectDefault();
  }

  getPaymentMethods = (active) => {
    const { cart } = this.props;
    const paymentMethods = [];

    if (active) {
      Object.entries(cart.cart.payment_methods).forEach(([, method]) => {
        // If payment method from api response not exists in
        // available payment methods in drupal, ignore it.
        if (method.code in drupalSettings.payment_methods) {
          paymentMethods[method.code] = drupalSettings.payment_methods[method.code];
        }
      });
    } else {
      const { cart } = this.props;

      Object.entries(drupalSettings.payment_methods).forEach(([, method]) => {
        if (!(cart.delivery_type !== undefined && cart.delivery_type === 'cnc' && method.code === 'cashondelivery')) {
          paymentMethods[method.code] = drupalSettings.payment_methods[method.code];
        }
      });
    }

    return paymentMethods;
  };

  changePaymentMethod = (method) => {
    const { cart } = this.props;
    if (!this.isActive() || cart.selected_payment_method === method) {
      return;
    }

    // If payment method we trying to set is same as
    // what set in cart, we don;t do anything.
    if (cart.cart.cart_payment_method !== undefined
      && cart.cart.cart_payment_method !== null
      && method === cart.cart.cart_payment_method) {
        document.getElementById(`payment-method-${method}`).checked = true;
        // Selected key is not set, we set.
        if (cart.selected_payment_method === undefined) {
          cart.selected_payment_method = method;
          addInfoInStorage(cart);
        }
        return;
    }

    showFullScreenLoader();

    const analytics = {};
    if (typeof window.ga === 'function' && window.ga.loaded) {
      analytics.clientId = ga.getAll()[0].get('clientId');
      analytics.trackingId = ga.getAll()[0].get('trackingId');
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

        const { cart: cartData, refreshCart } = this.props;
        cartData.selected_payment_method = method;
        cartData.cart = result;
        refreshCart(cartData);
      }).catch((error) => {
        console.error(error);
      });
    }
  };

  validateBeforePlaceOrder = () => {
    const { cart } = this.props;

    // Trigger validate of selected component.
    this.paymentMethodRefs[cart.selected_payment_method].current.validateBeforePlaceOrder();
  };

  render = () => {
    const methods = [];

    const active = this.isActive();
    const { cart, refreshCart } = this.props;

    Object.entries(this.getPaymentMethods(active)).forEach(([key, method]) => {
      this.paymentMethodRefs[method.code] = React.createRef();

      methods.push(<PaymentMethod
        cart={cart}
        ref={this.paymentMethodRefs[method.code]}
        refreshCart={refreshCart}
        changePaymentMethod={this.changePaymentMethod}
        isSelected={cart.selected_payment_method === method.code}
        key={key}
        method={method}
      />);
    });

    const activeClass = active ? 'active' : 'in-active';

    return (
      <div className="spc-checkout-payment-options">
        <ConditionalView condition={Object.keys(methods).length > 0}>
          <SectionTitle>{Drupal.t('Payment Methods')}</SectionTitle>
          <div className={`payment-methods ${activeClass}`}>{methods}</div>
        </ConditionalView>
      </div>
    );
  }
}
