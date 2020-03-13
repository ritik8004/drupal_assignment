import React from 'react';

import {
  placeOrder,
  isDeliveryTypeSameAsInCart
} from '../../../utilities/checkout_util';
import PriceElement from "../../../utilities/special-price/PriceElement";

export default class CompletePurchase extends React.Component {

  /**
   * Place order.
   */
  placeOrder = (e) => {
    e.preventDefault();

    // If purchase button is not clickable.
    if (!isDeliveryTypeSameAsInCart(this.props.cart)) {
      return false;
    }

    try {
      this.props.validateBeforePlaceOrder();
    }
    catch (error) {
      // Error 200 means everything is fine.
      // Place order will be done after payment validation is completed.
      if (error !== 200) {
        console.error(error);
      }

      return;
    }

    const { cart } = this.props;
    placeOrder(cart.cart.cart_id, cart.selected_payment_method);
  };

  render() {
    const { cart } = this.props;
    let class_name = isDeliveryTypeSameAsInCart(cart)
      ? 'active'
      : 'in-active';

    return (
      <div className={"checkout-link submit " + class_name}>
        {window.innerWidth < 768 &&
          <div className='order-preview'>
            <span className='total-count'> {Drupal.t('Order total (@count items)', {'@count': cart.cart.items_qty})} </span>
            <span className='total-price'> <PriceElement amount={cart.cart.cart_total}/> </span>
          </div>
        }
        <a href={Drupal.url('checkout')} className="checkout-link" onClick={(e) => this.placeOrder(e)}>
          {Drupal.t('complete purchase')}
        </a>
      </div>
    );
  }

}
