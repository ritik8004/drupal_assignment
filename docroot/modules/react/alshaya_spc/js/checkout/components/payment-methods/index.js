import React from 'react';

import SectionTitle from '../../../utilities/section-title';
import PaymentMethod from '../payment-method';
import {getPaymentMethods} from '../../../utilities/checkout_util';

export default class PaymentMethods extends React.Component {

  constructor(props) {
    super(props);
    this.state = {
      'payment_methods': this.props.payment_methods,
      'selected_payment_method': this.props.selected_payment_method
    };
  }

  componentDidMount() {
    // If shipping info is set for cart, only then get payment
    // methods for the cart.
    if (this.props.cart.carrier_info !== null) {
      let methods = getPaymentMethods(this.props.cart.cart_id);
      if (methods instanceof Promise) {
        methods.then((result) => {
          let payment_methods = new Array();
          Object.entries(result).forEach(([key, method]) => {
            // If payment method from api response not exists in
            // available payment methods in drupal.
            if (method['code'] in this.props.payment_methods) {
              payment_methods[method['code']] = this.props.payment_methods[method['code']];
            }
          });
          
          this.setState({
            payment_methods: payment_methods
          });
        });
      }
    }
  }

  render() {
    let methods = [];
    Object.entries(this.state.payment_methods).forEach(([key, method]) => {
      methods.push(<PaymentMethod selected_payment_method={this.props.selected_payment_method} key={key} method={method} payment_method_select={this.props.payment_method_select} />);
    });

    let active_class = this.props.is_active
      ? 'active'
      : 'in-active';

    return(
      <div className="spc-checkout-payment-options">
      	<SectionTitle>{Drupal.t('Payment methods')}</SectionTitle>
      	<div className={'payment-methods ' + active_class}>{methods}</div>
      </div>
    );
  }

}
