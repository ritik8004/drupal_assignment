import React from 'react';
import {
  placeOrder,
  isDeliveryTypeSameAsInCart,
} from '../../../utilities/checkout_util';
import PriceElement from '../../../utilities/special-price/PriceElement';
import dispatchCustomEvent from '../../../utilities/events';
import { smoothScrollTo } from '../../../utilities/smoothScroll';
import ConditionalView from '../../../common/components/conditional-view';
import ApplePayButton from '../payment-method-apple-pay/applePayButton';

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
      payment_method: cart.cart.payment.method,
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

    const checkoutButton = e.target.parentNode;
    checkoutButton.classList.add('in-active');

    try {
      const validated = validateBeforePlaceOrder();
      if (validated === false) {
        if (this.completePurchaseButtonActive()) {
          checkoutButton.classList.remove('in-active');
        }
        return;
      }

      placeOrder(cart.cart.payment.method);
    } catch (error) {
      Drupal.logJavascriptError('place-order', error);
    }
  };

  /**
   * To determine whether complete purchase button
   * should be active and clickable or not.
   */
  completePurchaseButtonActive = () => {
    const { cart } = this.props;

    // If delivery method selected same as what in cart.
    const deliverSameAsInCart = isDeliveryTypeSameAsInCart(cart);
    // If shipping info set in cart or not.
    const isShippingSet = (cart.cart.shipping.method !== null);
    // If billing info set in cart or not.
    let isBillingSet = false;
    if (cart.cart.billing_address !== null) {
      if (cart.cart.shipping.type === 'home_delivery') {
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
    const paymentMethod = cart.cart.payment.method !== undefined
      ? cart.cart.payment.method
      : '';

    return (
      <div className={`checkout-link fadeInUp notInMobile submit ${className} ${paymentMethod}`} style={{ animationDelay: '0.5s' }}>
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

        <ConditionalView condition={paymentMethod === 'checkout_com_applepay'}>
          <ApplePayButton isaActive={className} text={Drupal.t('Buy with')} lang={drupalSettings.path.currentLanguage} placeOrder={(e) => this.placeOrder(e)} />
        </ConditionalView>
        <ConditionalView condition={paymentMethod !== 'checkout_com_applepay'}>
          <a href={Drupal.url('checkout')} className="checkout-link" onClick={(e) => this.placeOrder(e)}>
            {Drupal.t('complete purchase')}
          </a>
        </ConditionalView>
      </div>
    );
  }
}
