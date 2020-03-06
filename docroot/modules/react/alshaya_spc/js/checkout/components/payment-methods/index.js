import React from 'react';

import SectionTitle from '../../../utilities/section-title';
import PaymentMethod from '../payment-method';
import {addPaymentMethodInCart} from "../../../utilities/update_cart";
import {showFullScreenLoader} from "../../../utilities/checkout_util";

export default class PaymentMethods extends React.Component {
  constructor(props) {
    super(props);

    const { cart } = this.props;
    const is_active = (cart.cart.carrier_info !== null);

    this.state = {
      active: is_active,
      selectedOption: cart.selected_payment_method,
      paymentMethods: new Array(),
    };
  }

  componentDidMount() {
    // If shipping info is set for cart, only then get payment
    // methods for the cart.
    if (this.props.cart.cart.carrier_info === null) {
      return;
    }

    const paymentMethods = new Array();
    Object.entries(drupalSettings.payment_methods).forEach(([key, method]) => {
      // If payment method from api response not exists in
      // available payment methods in drupal.
      if (method.code in this.props.paymentMethodsData) {
        paymentMethods[method.code] = this.props.paymentMethodsData[method.code];
      }
    });

    const prevState = this.state;
    this.setState({ ...prevState, paymentMethods: paymentMethods });

    // Select first payment method by default.
    if (this.props.cart.selected_payment_method === 'undefined'
      || !(this.props.cart.selected_payment_method)) {
      this.changePaymentMethod(Object.keys(paymentMethods)[0]);
    }
  }

  changePaymentMethod = (method) => {
    if (this.state.selectedOption === method) {
      return;
    }

    showFullScreenLoader();

    const prevState = this.state;
    this.setState({ ...prevState, selectedOption: method });

    document.getElementById('payment-method-' + method).checked = true;

    let data = {
      'payment' : {
        'method': method,
        'additional_data': {}
      }
    };
    let cart = addPaymentMethodInCart('update payment', data);
    if (cart instanceof Promise) {
      cart.then((result) => {
        let cart_data = this.props.cart;
        cart_data['selected_payment_method'] = method;
        cart_data['cart'] = result;
        this.props.refreshCart(cart_data);
      });
    }
  };

  render() {
    const methods = [];
    let i = 0;
    Object.entries(this.state.paymentMethods).forEach(([key, method]) => {
      let isSelected = false;
      if (!isSelected && this.props.cart.selected_payment_method !== undefined
        && this.props.cart.selected_payment_method === key) {
        isSelected = key;
      } else if (i === 0 && !isSelected) {
        isSelected = key;
      }
      i++;
      methods.push(<PaymentMethod cart={this.props.cart} refreshCart={this.props.refreshCart} changePaymentMethod={this.changePaymentMethod} isSelected={isSelected} key={key} method={method} />);
    });

    const active_class = this.state.active
      ? 'active'
      : 'in-active';

    return (
      <div className="spc-checkout-payment-options">
        <SectionTitle>{Drupal.t('payment methods')}</SectionTitle>
        <div className={`payment-methods ${active_class}`}>{methods}</div>
      </div>
    );
  }
}
