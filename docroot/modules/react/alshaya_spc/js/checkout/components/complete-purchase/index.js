import React from 'react';

import {placeOrder} from '../../../utilities/checkout_util';
import Price from '../../../utilities/price';

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
        {window.innerWidth < 768 &&
          <div className='order-preview'>
            <span className='total-count'> {Drupal.t('Order total (@count items)', {'@count': this.props.cart.items_qty})} </span>
            <span className='total-price'> <Price price={this.props.cart.cart_total}/> </span>
          </div>
        }
        <a href={Drupal.url('checkout')} className="checkout-link" onClick={(e) => this.placeOrder(e)}>
          {Drupal.t('complete purchase')}
        </a>
      </div>
    );
  }

}
