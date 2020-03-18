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
    const { cart, validateBeforePlaceOrder } = this.props;
    // If purchase button is not clickable.
    if (!this.completePurchaseButtonActive(cart)) {
      return;
    }

    try {
      const validated = validateBeforePlaceOrder();
      if (validated === false) {
        return;
      }

      placeOrder(cart.selected_payment_method);
    } catch (error) {
      console.error(error);
    }
  };

  /**
   * To determone whether complete purchase button
   * should be active and clickable or not.
   */
  completePurchaseButtonActive = (cart) => {
    // If delivery method selected same as what in cart.
    const deliverSameAsInCart = isDeliveryTypeSameAsInCart(cart);
    // If shipping info set in cart or not.
    const isShippingSet = (cart.cart.carrier_info !== null);
    // If billing info set in cart or not.
    let isBillingSet = false;
    if (cart.cart.billing_address !== null) {
      if (cart.cart.delivery_type === 'hd') {
        isBillingSet = true;
      } else if (cart.cart.billing_address.city.length > 0
        && cart.cart.billing_address.city !== 'NONE') {
        // For CnC, user needs to actually fill the billing address.
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
    const className = this.completePurchaseButtonActive(cart)
      ? 'active'
      : 'in-active';

    return (
      <div className={`checkout-link submit ${className}`}>
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
