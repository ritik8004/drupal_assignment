import React from 'react';

import SectionTitle from '../../../utilities/section-title';
import PaymentMethod from '../payment-method';
import {addPaymentMethodInCart} from "../../../utilities/update_cart";
import {showFullScreenLoader} from "../../../utilities/checkout_util";
import ConditionalView from "../../../common/components/conditional-view";

export default class PaymentMethods extends React.Component {
  constructor(props) {
    super(props);

    this.paymentMethodRefs = new Array();

    this.state = {
      active: (props.cart.carrier_info !== null),
      selectedOption: props.cart.selected_payment_method,
      availablePaymentMethods: props.cart.cart.payment_methods,
      paymentMethods: new Array(),
    };
  }

  selectDefault = () => {
    if (Object.keys(this.state.paymentMethods).length === 0) {
      return;
    }

    // Select first payment method by default.
    if (this.props.cart.selected_payment_method === 'undefined'
      || !(this.props.cart.selected_payment_method)) {
      this.changePaymentMethod(Object.keys(this.state.paymentMethods)[0]);
    }
  };

  componentDidMount() {
    document.addEventListener('refreshCartOnAddress', this.eventListener, false);
    document.addEventListener('refreshCartOnCnCSelect', this.eventListener, false);
    this.selectDefault();
  };

  componentWillUnmount() {
    document.removeEventListener('refreshCartOnAddress', this.eventListener, false);
    document.removeEventListener('refreshCartOnCnCSelect', this.eventListener, false);
  }

  eventListener = (e) => {
    this.setState({
      active: (this.props.cart.carrier_info !== null),
      selectedOption: this.props.cart.selected_payment_method,
      availablePaymentMethods: this.props.cart.cart.payment_methods,
      paymentMethods: new Array(),
    });

    this.selectDefault();
  };

  getPaymentMethods = () => {
    if (Object.keys(this.state.paymentMethods).length > 0) {
      return this.state.paymentMethods;
    }

    if (this.state.availablePaymentMethods.length === 0) {
      return this.state.paymentMethods;
    }

    Object.entries(this.state.availablePaymentMethods).forEach(([key, method]) => {
      // If payment method from api response not exists in
      // available payment methods in drupal, ignore it.
      if (method.code in drupalSettings.payment_methods) {
        this.state.paymentMethods[method.code] = drupalSettings.payment_methods[method.code];
      }
    });

    return this.state.paymentMethods;
  };

  changePaymentMethod = (method) => {
    if (this.state.selectedOption === method) {
      return;
    }

    showFullScreenLoader();

    let data = {
      'payment' : {
        'method': method,
        'additional_data': {}
      }
    };
    let cart = addPaymentMethodInCart('update payment', data);
    if (cart instanceof Promise) {
      cart.then((result) => {
        // @TODO: Handle exception.
        document.getElementById('payment-method-' + method).checked = true;
        this.setState({ ...this.state, selectedOption: method });

        let cart_data = this.props.cart;
        cart_data['selected_payment_method'] = method;
        cart_data['cart'] = result;
        this.props.refreshCart(cart_data);
      }).catch((error) => {
        console.error(error);
      });
    }
  };

  validateBeforePlaceOrder = () => {
    // Trigger validate of selected component.
    this.paymentMethodRefs[this.state.selectedOption].validateBeforePlaceOrder();
  };

  render() {
    const methods = [];

    Object.entries(this.getPaymentMethods()).forEach(([key, method]) => {
      this.paymentMethodRefs[method] = React.createRef();
      let isSelected = (this.state.selectedOption === key) ? key : '';
      methods.push(<PaymentMethod cart={this.props.cart}
                                  ref={this.paymentMethodRefs[method]}
                                  refreshCart={this.props.refreshCart}
                                  changePaymentMethod={this.changePaymentMethod}
                                  isSelected={isSelected} key={key}
                                  method={method}/>);
    });

    const active_class = this.state.active
      ? 'active'
      : 'in-active';

    return (
      <div className="spc-checkout-payment-options">
        <ConditionalView condition={Object.keys(methods).length > 0}>
          <SectionTitle>{Drupal.t('payment methods')}</SectionTitle>
          <div className={`payment-methods ${active_class}`}>{methods}</div>
        </ConditionalView>
      </div>
    );
  }
}
