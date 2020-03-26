import React from 'react';
import {
  placeOrder,
  isDeliveryTypeSameAsInCart,
} from '../../../utilities/checkout_util';
import PriceElement from '../../../utilities/special-price/PriceElement';
import dispatchCustomEvent from '../../../utilities/events';
import smoothScrollTo from '../../../utilities/smoothScroll';

export default class CompletePurchase extends React.Component {
  constructor(props) {
    super(props);

    // We use the state the trigger render again.
    this.state = {
      status: false,
    };
  }

  componentDidMount() {
    document.addEventListener(
      'refreshCompletePurchaseSection',
      this.refreshCompletePurchaseSection,
      false,
    );
  }

  componentWillUnmount() {
    document.removeEventListener(
      'refreshCompletePurchaseSection',
      this.refreshCompletePurchaseSection,
      false,
    );
  }

  refreshCompletePurchaseSection = () => {
    const { status } = this.state;
    const currentStatus = this.completePurchaseButtonActive();
    if (currentStatus !== status) {
      this.setState({ status: currentStatus });
    }
  };

  /**
   * Place order.
   */
  placeOrder = (e) => {
    e.preventDefault();
    const { cart, validateBeforePlaceOrder } = this.props;

    dispatchCustomEvent('orderPaymentMethod', {
      payment_method: cart.cart.cart_payment_method,
    });

    // If purchase button is not clickable.
    if (!this.completePurchaseButtonActive()) {
      // Scroll to first payment section if error exists there.
      if (document.getElementById('spc-payment-methods') === null
      || document.getElementById('spc-payment-methods').querySelectorAll('.error').length > 0) {
        smoothScrollTo('#spc-payment-methods');
      }

      return;
    }

    try {
      const validated = validateBeforePlaceOrder();
      if (validated === false) {
        return;
      }

      placeOrder(cart.cart.cart_payment_method);
    } catch (error) {
      Drupal.logJavascriptError('place-order', error);
    }
  };

  /**
   * To determone whether complete purchase button
   * should be active and clickable or not.
   */
  completePurchaseButtonActive = () => {
    const { cart } = this.props;

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

    // Disabled if there is still some error left in payment form.
    if (document.getElementById('spc-payment-methods') === null
      || document.getElementById('spc-payment-methods').querySelectorAll('.error').length > 0) {
      return false;
    }

    // If all conditions are true only then purchase button is
    // active and clickable.
    if (deliverSameAsInCart && isShippingSet && isBillingSet) {
      return true;
    }

    return false;
  };

  render() {
    const { cart } = this.props;
    const className = this.completePurchaseButtonActive()
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
