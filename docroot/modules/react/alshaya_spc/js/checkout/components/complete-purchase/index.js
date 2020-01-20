import React from 'react';

import {placeOrder} from '../../../utilities/checkout_util';

export default class CompletePurchase extends React.Component {

  placeOrder = (e) => {
    e.preventDefault();
    let payment_method = this.props.selected_payment_method;
    placeOrder(this.props.cart.cart_id, payment_method);
  }

  render() {
    let class_name = this.props.selected_payment_method !== null
      ? 'active'
      : 'in-active';
    return (
      <div className={"checkout-link submit " + class_name}>
        <a href={Drupal.url('checkout')} className="checkout-link" onClick={(e) => this.placeOrder(e)}>
          {Drupal.t('complete purchase')}
        </a>
      </div>
    );
  }

}
