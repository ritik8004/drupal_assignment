import React from 'react';

import SectionTitle from '../../../utilities/section-title';
import PaymentMethod from '../payment-method';
import { getPaymentMethods } from '../../../utilities/checkout_util';

export default class PaymentMethods extends React.Component {
  constructor(props) {
    super(props);

    const { cart } = this.props;
    const is_active = (cart.cart.carrier_info !== null);

    this.state = {
      payment_methods: this.props.payment_methods,
      active: is_active,
    };
  }

  componentDidMount() {
    // If shipping info is set for cart, only then get payment
    // methods for the cart.
    if (this.props.cart.cart.carrier_info !== null) {
      const methods = getPaymentMethods(this.props.cart.cart.cart_id);
      if (methods instanceof Promise) {
        methods.then((result) => {
          const payment_methods = new Array();
          Object.entries(result).forEach(([key, method]) => {
            // If payment method from api response not exists in
            // available payment methods in drupal.
            if (method.code in this.props.payment_methods) {
              payment_methods[method.code] = this.props.payment_methods[method.code];
            }
          });

          this.setState({
            payment_methods,
          });
        });
      }
    }
  }

  render() {
    const methods = [];
    let i = 0;
    Object.entries(this.state.payment_methods).forEach(([key, method]) => {
      let isSelected = false;
      if (!isSelected && this.props.cart.selected_payment_method !== undefined
        && this.props.cart.selected_payment_method === key) {
        isSelected = key;
      } else if (i === 0 && !isSelected) {
        isSelected = key;
      }
      i++;
      methods.push(<PaymentMethod cart={this.props.cart} refreshCart={this.props.refreshCart} isSelected={isSelected} key={key} method={method} />);
    });

    const active_class = this.state.active
      ? 'active'
      : 'in-active';

    return (
      <div className="spc-checkout-payment-options">
        <SectionTitle>{Drupal.t('Payment methods')}</SectionTitle>
        <div className={`payment-methods ${active_class}`}>{methods}</div>
      </div>
    );
  }
}
