import React from 'react';
import {
  placeOrder,
  isDeliveryTypeSameAsInCart,
  isShippingMethodSet,
} from '../../../utilities/checkout_util';
import dispatchCustomEvent from '../../../utilities/events';
import { smoothScrollTo } from '../../../utilities/smoothScroll';
import ConditionalView from '../../../common/components/conditional-view';
import ApplePayButton from '../payment-method-apple-pay/applePayButton';
import { isFullPaymentDoneByEgift } from '../../../utilities/egift_util';
import { addPaymentMethodInCart } from '../../../utilities/update_cart';
import { removeFullScreenLoader } from '../../../../../js/utilities/showRemoveFullScreenLoader';
import getStringMessage from '../../../utilities/strings';

export default class CompletePurchase extends React.Component {
  componentDidMount() {
    document.addEventListener('updatePlaceOrderCTA', this.updatePlaceOrderCTA, false);
    document.addEventListener('orderPlaced', this.orderPlaced);
  }

  componentWillUnmount() {
    document.removeEventListener('updatePlaceOrderCTA', this.updatePlaceOrderCTA, false);
  }

  /**
   * Callback for any action after an order is placed.
   */
  orderPlaced = () => {
    // 'benefit_pay_modal_auto_opened' is used to ensure that we auto open the
    // benefit pay modal only once for a user. Removing this key just after
    // placing order to remove old value.
    localStorage.removeItem('benefit_pay_modal_auto_opened');
  }

  /**
   * Update the 'complete purchase' CTA button to active/inactive.
   *
   * @param event
   */
  updatePlaceOrderCTA = (event) => {
    const { status } = event.detail;
    if (status === true) {
      // Mark the CTA active.
      const completePurchaseCTA = document.querySelector('.complete-purchase-cta');
      completePurchaseCTA.classList.remove('in-active');
    }
  }

  /**
   * Place order.
   */
  placeOrder = async (e) => {
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
      // Dispatch the event for all payment method
      // except checkout_com_upapi method.
      // For checkout_com_upapi method this
      // handle in its own component.
      if (cart.cart.payment.method !== 'checkout_com_upapi') {
        dispatchCustomEvent('orderPaymentMethod', {
          payment_method: Object
            .values(drupalSettings.payment_methods)
            .filter((paymentMethod) => (paymentMethod.code === cart.cart.payment.method))
            .shift().gtm_name,
        });
      }
    } else {
      // If cart payment method is not in drupalSettings,
      // then it's a pseudo payment method.
      isPseudoPaymentMedthod = true;
      dispatchCustomEvent('orderPaymentMethod', {
        payment_method: cart.cart.payment.method,
      });
    }

    // If full payment is done by egift card then treat it as PseudoPayment
    // method.
    if (isFullPaymentDoneByEgift(cart.cart)) {
      isPseudoPaymentMedthod = true;
    }

    const checkoutButton = e.target.parentNode;
    checkoutButton.classList.add('in-active');

    try {
      const validated = (isPseudoPaymentMedthod === false)
        ? await validateBeforePlaceOrder()
        : true;

      if (validated === false) {
        if (this.completePurchaseButtonActive()) {
          checkoutButton.classList.remove('in-active');
        }
        return;
      }

      // If full payment is done by egift then change the payment method to
      // hps_payment.
      let paymentMethod = cart.cart.payment.method;
      if (isFullPaymentDoneByEgift(cart.cart)) {
        paymentMethod = 'hps_payment';

        const analytics = Drupal.alshayaSpc.getGAData();

        const data = {
          payment: {
            method: paymentMethod,
            additional_data: {},
            analytics,
          },
        };

        const cartUpdate = addPaymentMethodInCart('update payment', data);
        if (cartUpdate instanceof Promise) {
          cartUpdate.then((result) => {
            if (!result) {
              // Close popup in case of error.
              removeFullScreenLoader();

              dispatchCustomEvent('spcCheckoutMessageUpdate', {
                type: 'error',
                message: getStringMessage('global_error'),
              });
            } else {
              placeOrder(paymentMethod);
            }
          }).catch((error) => {
            Drupal.logJavascriptError('change payment method', error, GTM_CONSTANTS.GENUINE_PAYMENT_ERRORS);
          });
        }
      } else {
        placeOrder(paymentMethod);
      }
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
    const isShippingSet = isShippingMethodSet(cart);
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
    if ((document.getElementById('spc-payment-methods') === null
      || document.getElementById('spc-payment-methods').querySelectorAll('.error').length > 0)
      // Bypass the payment method error check if the full payment is done by
      // egift card.
      && !isFullPaymentDoneByEgift(cart.cart)) {
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

    let buttonText = '';

    switch (paymentMethod) {
      case 'postpay':
        buttonText = Drupal.t('Continue with postpay');
        break;
      case 'checkout_com_upapi_benefitpay':
        buttonText = Drupal.t('Continue with Benefit pay');
        break;
      case 'tabby':
        buttonText = Drupal.t('Continue with tabby');
        break;
      default:
        buttonText = Drupal.t('complete purchase');
    }

    return (
      <div className={`checkout-link complete-purchase complete-purchase-cta fadeInUp notInMobile submit active ${paymentMethod}`} style={{ animationDelay: '0.5s' }}>
        <ConditionalView condition={paymentMethod === 'checkout_com_applepay' || paymentMethod === 'checkout_com_upapi_applepay'}>
          <ApplePayButton isaActive="active" text={Drupal.t('Buy with')} lang={drupalSettings.path.currentLanguage} placeOrder={(e) => this.placeOrder(e)} />
        </ConditionalView>
        <ConditionalView condition={paymentMethod !== 'checkout_com_applepay' && paymentMethod !== 'checkout_com_upapi_applepay'}>
          <a href={Drupal.url('checkout')} className="checkout-link" onClick={(e) => this.placeOrder(e)}>
            {buttonText}
          </a>
        </ConditionalView>
      </div>
    );
  }
}
