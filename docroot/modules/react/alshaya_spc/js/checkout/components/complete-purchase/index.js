import React from 'react';

import {
  placeOrder,
  isDeliveryTypeSameAsInCart,
} from '../../../utilities/checkout_util';
import PriceElement from '../../../utilities/special-price/PriceElement';

export default class CompletePurchase extends React.Component {
  /**
   * Place order.
   */
  placeOrder = (e) => {
    e.preventDefault();

    // If purchase button is not clickable.
    if (!this.completePurchaseButtonActive(this.props.cart)) {
      return false;
    }

    try {
      this.props.validateBeforePlaceOrder();
    } catch (error) {
      // Error 200 means everything is fine.
      // Place order will be done after payment validation is completed.
      if (error !== 200) {
        console.error(error);
      }

      return;
    }

    const { cart } = this.props;
    placeOrder(cart.cart, cart.selected_payment_method);
  };

  /**
   * To determone whether complete purchase button
   * should be active and clickable or not.
   */
  completePurchaseButtonActive = (cart) => {
    // If delivery method selected same as what in cart.
    const deliverSameAsInCart = isDeliveryTypeSameAsInCart(cart);
    // If shiiping info set in cart or not.
    const isShippingSet = (cart.cart.carrier_info !== null);
    // If billing info set in cart or not.
    let isBillingSet = false;
    if (cart.cart.billing_address !== null) {
      if (cart.cart.delivery_type === 'hd') {
        isBillingSet = true;
      }
      // For CnC, user needs to actually fill the billing address.
      else if (cart.cart.billing_address.city.length > 0
        && cart.cart.billing_address.city !== 'NONE') {
        isBillingSet = true;
      }
    }

    // If all conditions are true only then purchase button is
    // active and clickable.
    if (deliverSameAsInCart
      && isShippingSet
      && isBillingSet
    ) {
      return true;
    }

    return false;
  }

  render() {
    const { cart } = this.props;
    const class_name = this.completePurchaseButtonActive(cart)
      ? 'active'
      : 'in-active';

    return (
      <div className={`checkout-link submit ${class_name}`}>
        {window.innerWidth < 768
          && (
          <div className="order-preview">
            <span className="total-count">
              {' '}
              {Drupal.t('Order total (@count items)', { '@count': cart.cart.items_qty })}
              {' '}
            </span>
            <span className="total-price">
              {' '}
              <PriceElement amount={cart.cart.cart_total} />
              {' '}
            </span>
          </div>
          )}
        <a href={Drupal.url('checkout')} className="checkout-link" onClick={(e) => this.placeOrder(e)}>
          {Drupal.t('complete purchase')}
        </a>
      </div>
    );
  }
}
