import React from 'react';

import {placeOrder} from '../../../utilities/checkout_util';

export default class CompletePurchase extends React.Component {

  placeOrder = () => {
    console.log('placing order');
    let payment_method = this.props.selected_payment_method;
    placeOrder(this.props.cart.cart_id, payment_method);
  }

  render() {
    let class_name = this.props.selected_payment_method !== null
      ? 'active'
      : 'in-active';
    return (
      <div className={"checkout-link submit " + class_name} onClick={() => this.placeOrder()}>
        <a href={Drupal.url('checkout')} className="checkout-link">
          {Drupal.t('complete purchase')}
        </a>
      </div>
    );
  }

}
