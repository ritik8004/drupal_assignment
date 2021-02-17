import React from 'react';
import {
  placeOrder,
  isDeliveryTypeSameAsInCart,
} from '../../../utilities/checkout_util';
import dispatchCustomEvent from '../../../utilities/events';
import { smoothScrollTo } from '../../../utilities/smoothScroll';
import ConditionalView from '../../../common/components/conditional-view';
import ApplePayButton from '../payment-method-apple-pay/applePayButton';

export default class CompletePurchase extends React.Component {
  /**
   * Place order.
   */
  placeOrder = (e) => {
    e.preventDefault();
    const { cart, validateBeforePlaceOrder } = this.props;

    if (!this.completePurchaseButtonActive()) {
      return;
    }

    // Flag to track pseudo payment method.
    let isPseudoPaymentMedthod = false;

    // Check if payment method in cart is a pseudo method
    // or not and accordingly dispatch event.
    if (drupalSettings.payment_methods[cart.cart.payment.method]) {
      dispatchCustomEvent('orderPaymentMethod', {
        payment_method: Object
          .values(drupalSettings.payment_methods)
          .filter((paymentMethod) => (paymentMethod.code === cart.cart.payment.method))
          .shift().gtm_name,
      });
    } else {
      // If cart payment method is not in drupalSettings,
      // then it's a pseudo payment method.
      isPseudoPaymentMedthod = true;
      dispatchCustomEvent('orderPaymentMethod', {
        payment_method: cart.cart.payment.method,
      });
    }

    const checkoutButton = e.target.parentNode;
    checkoutButton.classList.add('in-active');

    try {
      const validated = (isPseudoPaymentMedthod === false)
        ? validateBeforePlaceOrder()
        : true;

      if (validated === false) {
        if (this.completePurchaseButtonActive()) {
          checkoutButton.classList.remove('in-active');
        }
        return;
      }

      placeOrder(cart.cart.payment.method);
    } catch (error) {
      Drupal.logJavascriptError('place-order', error, GTM_CONSTANTS.CHECKOUT_ERRORS);
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

    // Scroll to the delivery information section.
    if (!deliverSameAsInCart || !isShippingSet) {
      // Adding error class in the section.
      const deliveryInfo = document.getElementsByClassName('spc-checkout-delivery-information');
      const deliveryInfoError = document.getElementById('delivery-information-error');
      smoothScrollTo('.spc-checkout-delivery-information');
      if (deliveryInfo.length !== 0 && deliveryInfoError === null) {
        const tag = document.createElement('p');
        let errorMessage;
        if (cart.delivery_type === 'click_and_collect') {
          errorMessage = document.createTextNode(Drupal.t('Please select the collection store'));
        } else {
          errorMessage = document.createTextNode(Drupal.t('Please add delivery information'));
        }
        tag.appendChild(errorMessage);
        deliveryInfo[0].appendChild(tag);
        tag.setAttribute('id', 'delivery-information-error');
      }
      return false;
    }

    // Disabled if there is still some error left in payment form.
    if (document.getElementById('spc-payment-methods') === null
      || document.getElementById('spc-payment-methods').querySelectorAll('.error').length > 0) {
      // Adding error class in the section.
      const paymentMethods = document.getElementById('spc-payment-methods');
      if (paymentMethods) {
        smoothScrollTo('#spc-payment-methods');
        const allInputs = document.querySelectorAll('#spc-payment-methods input');
        for (let x = 0; x < allInputs.length; x++) {
          // Trigerring payment card errors.
          const ev = new Event('blur', { bubbles: true });
          ev.simulated = true;
          allInputs[x].dispatchEvent(ev);
        }
      }
      return false;
    }

    // Scroll to the billing address section.
    if (!isBillingSet) {
      // Adding error class in the section.
      const billingAddress = document.getElementsByClassName('spc-section-billing-address');
      const billingAddressError = document.getElementById('billing-address-information-error');
      smoothScrollTo('.spc-section-billing-address');
      if (billingAddress !== 0 && billingAddressError === null) {
        const tag = document.createElement('p');
        const errorMessage = document.createTextNode(Drupal.t('Please add billing address information'));
        tag.appendChild(errorMessage);
        billingAddress[0].appendChild(tag);
        tag.setAttribute('id', 'billing-address-information-error');
      }
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
    const paymentMethod = cart.cart.payment.method !== undefined
      ? cart.cart.payment.method
      : '';

    return (
      <div className={`checkout-link complete-purchase fadeInUp notInMobile submit active ${paymentMethod}`} style={{ animationDelay: '0.5s' }}>
        <ConditionalView condition={paymentMethod === 'checkout_com_applepay' || paymentMethod === 'checkout_com_upapi_applepay'}>
          <ApplePayButton isaActive="active" text={Drupal.t('Buy with')} lang={drupalSettings.path.currentLanguage} placeOrder={(e) => this.placeOrder(e)} />
        </ConditionalView>
        <ConditionalView condition={paymentMethod !== 'checkout_com_applepay' && paymentMethod !== 'checkout_com_upapi_applepay'}>
          <a href={Drupal.url('checkout')} className="checkout-link" onClick={(e) => this.placeOrder(e)}>
            {Drupal.t('complete purchase')}
          </a>
        </ConditionalView>
      </div>
    );
  }
}
