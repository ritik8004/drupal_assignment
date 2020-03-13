import React from 'react';

import SectionTitle from '../../../utilities/section-title';
import PaymentMethod from '../payment-method';
import { addPaymentMethodInCart } from '../../../utilities/update_cart';
import {
  isDeliveryTypeSameAsInCart,
  showFullScreenLoader,
} from '../../../utilities/checkout_util';
import ConditionalView from '../../../common/components/conditional-view';

export default class PaymentMethods extends React.Component {
  constructor(props) {
    super(props);

    const { cart } = props;

    this.paymentMethodRefs = [];

    this.state = {
      selectedOption: cart.selected_payment_method,
      availablePaymentMethods: cart.cart.payment_methods,
    };
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
    document.addEventListener('refreshCartOnAddress', this.eventListener, false);
    document.addEventListener('refreshCartOnCnCSelect', this.eventListener, false);
    this.selectDefault();
  };

  componentDidUpdate(prevProps, prevState, snapshot) {
    this.selectDefault();
  }

  componentWillUnmount = () => {
    document.removeEventListener('refreshCartOnAddress', this.eventListener, false);
    document.removeEventListener('refreshCartOnCnCSelect', this.eventListener, false);
  };

  eventListener = () => {
    const { cart } = this.props;

    this.setState({
      selectedOption: cart.selected_payment_method,
      availablePaymentMethods: cart.cart.payment_methods,
    });

    this.selectDefault();
  };

  getPaymentMethods = (active) => {
    const { availablePaymentMethods } = this.state;

    const paymentMethods = [];

    if (active) {
      Object.entries(availablePaymentMethods).forEach(([, method]) => {
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
    const { selectedOption } = this.state;
    if (!this.isActive() || selectedOption === method) {
      return;
    }

    showFullScreenLoader();

    const data = {
      payment: {
        method,
        additional_data: {},
      },
    };

    const cart = addPaymentMethodInCart('update payment', data);
    if (cart instanceof Promise) {
      cart.then((result) => {
        // @TODO: Handle exception.
        document.getElementById(`payment-method-${method}`).checked = true;

        const prevState = this.state;
        this.setState({ ...prevState, selectedOption: method });

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
    const { selectedOption } = this.state;

    // Trigger validate of selected component.
    this.paymentMethodRefs[selectedOption].current.validateBeforePlaceOrder();
  };

  render = () => {
    const methods = [];

    const active = this.isActive();
    const { selectedOption } = this.state;
    const { cart, refreshCart } = this.props;

    Object.entries(this.getPaymentMethods(active)).forEach(([key, method]) => {
      this.paymentMethodRefs[method.code] = React.createRef();

      methods.push(<PaymentMethod
        cart={cart}
        ref={this.paymentMethodRefs[method.code]}
        refreshCart={refreshCart}
        changePaymentMethod={this.changePaymentMethod}
        isSelected={selectedOption === method.code}
        key={key}
        method={method}
      />);
    });

    const activeClass = active ? 'active' : 'in-active';

    return (
      <div className="spc-checkout-payment-options">
        <ConditionalView condition={Object.keys(methods).length > 0}>
          <SectionTitle>{Drupal.t('payment methods')}</SectionTitle>
          <div className={`payment-methods ${activeClass}`}>{methods}</div>
        </ConditionalView>
      </div>
    );
  }
}
