import React from 'react';
import {
  placeOrder,
  isDeliveryTypeSameAsInCart,
  isShippingMethodSet,
  updatePaymentAndPlaceOrder,
} from '../../../utilities/checkout_util';
import dispatchCustomEvent from '../../../utilities/events';
import { smoothScrollTo } from '../../../utilities/smoothScroll';
import ConditionalView from '../../../common/components/conditional-view';
import ApplePayButton from '../payment-method-apple-pay/applePayButton';
import {
  cartContainsAnyNormalProduct,
  cartContainsAnyVirtualProduct,
  isEgiftRedemptionDone,
  isFullPaymentDoneByEgift,
} from '../../../utilities/egift_util';
import { isEgiftCardEnabled, isFullPaymentDoneByPseudoPaymentMedthods } from '../../../../../js/utilities/util';
import isAuraEnabled from '../../../../../js/utilities/helper';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';
import { getBookingDetailByConfirmationNumber, getBookingDetailByConfirmationNumberSynchronous } from '../../../../../js/utilities/onlineBookingHelper';
import { isAuraIntegrationEnabled } from '../../../../../js/utilities/helloMemberHelper';

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
    Drupal.removeItemFromLocalStorage('benefit_pay_modal_auto_opened');
    // Remove the topup quote id if exists in the local storage.
    if (isEgiftCardEnabled()) {
      Drupal.removeItemFromLocalStorage('topupQuote');
    }
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

    // If full payment is done by egift or egift + AURA then change the payment
    // method to hps_payment.
    let cartPaymentMethod = cart.cart.payment.method;
    if (isEgiftCardEnabled()
      && (isFullPaymentDoneByEgift(cart.cart)
      || isFullPaymentDoneByPseudoPaymentMedthods(cart.cart))
      && cart.cart.payment.method !== 'hps_payment') {
      cartPaymentMethod = 'hps_payment';
    }

    // Check if payment method in cart is a pseudo method
    // or not and accordingly dispatch event.
    if (drupalSettings.payment_methods[cartPaymentMethod]) {
      // Dispatch the event for all payment method
      // except checkout_com_upapi method.
      // For checkout_com_upapi method this
      // handle in its own component.
      if (cartPaymentMethod !== 'checkout_com_upapi') {
        dispatchCustomEvent('orderPaymentMethod', {
          payment_method: Object
            .values(drupalSettings.payment_methods)
            .filter((paymentMethod) => (paymentMethod.code === cartPaymentMethod))
            .shift().gtm_name,
        });
      }
    } else {
      // If cart payment method is not in drupalSettings,
      // then it's a pseudo payment method.
      isPseudoPaymentMedthod = true;
      dispatchCustomEvent('orderPaymentMethod', {
        payment_method: cartPaymentMethod,
      });
    }

    const checkoutButton = e.target.parentNode;
    checkoutButton.classList.add('in-active');

    // Check if the cart is having online booking confirmation number.
    // Validate the booking is expired and show error accordingly.
    if (hasValue(cart.cart.hfd_hold_confirmation_number)) {
      // Check if the hold appointment for user is valid.
      // And avoid async calls for Apple pay.
      const bookingDetails = (cartPaymentMethod === 'checkout_com_upapi_applepay' || cartPaymentMethod === 'checkout_com_applepay')
        ? getBookingDetailByConfirmationNumberSynchronous(cart.cart.hfd_hold_confirmation_number)
        : await getBookingDetailByConfirmationNumber(cart.cart.hfd_hold_confirmation_number);

      // Check if success return false,
      if (!hasValue(bookingDetails.status) && bookingDetails.error_code === 0) {
        dispatchCustomEvent('validateOnlineBookingPurchase', {
          bookingDetails,
        });
        // Activate place order button.
        checkoutButton.classList.remove('in-active');
        // Scroll the user to delivery information section.
        smoothScrollTo('.spc-checkout-delivery-information');
        return;
      }
    }

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

      // To add the custom event for the checkout step 4.
      dispatchCustomEvent('orderValidated', {
        cart: cart.cart,
        cartPaymentMethod,
      });

      // If full payment is done by egift or egift + AURA then change the
      // payment method to hps_payment.
      if (isEgiftCardEnabled()
        && (isFullPaymentDoneByEgift(cart.cart)
        || isFullPaymentDoneByPseudoPaymentMedthods(cart.cart))
        && cart.cart.payment.method !== cartPaymentMethod) {
        // Change payment method to hps_payment and place order.
        updatePaymentAndPlaceOrder(cartPaymentMethod);
      } else {
        placeOrder(cartPaymentMethod);
      }
    } catch (error) {
      Drupal.logJavascriptError('place-order', error, GTM_CONSTANTS.CHECKOUT_ERRORS);
    }
  };

  /**
   * To determine whether complete purchase button
   * should be active and clickable or not.
   *
   * @param {boolean}
   *  To determine if this is called when rendering the component
   *  or when placing the order.
   */
  completePurchaseButtonActive = (showErrors = true) => {
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

    // If showErrors is false and delivery info is not set return
    // false and don't show any error messages.
    if (!showErrors
      && ((!deliverSameAsInCart
      || !isShippingSet)
      || !isBillingSet)) {
      return false;
    }
    // If showErrors is false and delivery info is set return
    // true immediately.
    if (!showErrors
      && deliverSameAsInCart
      && isShippingSet
      && isBillingSet) {
      return true;
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

    // Disabled if there is still some error left in payment form
    // except for hps payment method error.
    if (document.getElementById('spc-payment-methods') === null
      || document.getElementById('spc-payment-methods').querySelectorAll('.error:not(.linked-card-payment-error)').length > 0) {
      // Bypass the payment method error check if the full payment is done by
      // egift car or full payment is done by Egift + AURA.
      if (!((isEgiftCardEnabled() && isFullPaymentDoneByEgift(cart.cart))
        || isFullPaymentDoneByPseudoPaymentMedthods(cart.cart))) {
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

    // If somehow user bypass the frontend checks and tries to place order, then
    // we are validating that user has done full payment by egift and aura and
    // balance payable is greater than 0.
    const { balancePayable, paidWithAura, egiftRedeemedAmount } = cart.cart.totals;
    if (isEgiftCardEnabled()
      && (isAuraEnabled() || isAuraIntegrationEnabled())
      && paidWithAura > 0
      && egiftRedeemedAmount > 0
      && balancePayable > 0) {
      dispatchCustomEvent('spcCheckoutMessageUpdate', {
        type: 'error',
        message: drupalSettings.globalErrorMessage,
      });
      return false;
    }

    // If somehow user bypass the frontend checks and tries to place order, then
    // here we will validate if cart contains normal + digital product and user
    // has redeem partial points from egift.
    if (isEgiftCardEnabled()
      && cartContainsAnyNormalProduct(cart.cart)
      && cartContainsAnyVirtualProduct(cart.cart)
      && (isEgiftRedemptionDone(cart.cart)
      || isEgiftRedemptionDone(cart.cart, 'linked'))
      && !isFullPaymentDoneByEgift(cart.cart)) {
      dispatchCustomEvent('spcCheckoutMessageUpdate', {
        type: 'error',
        message: Drupal.t('Please pay full amount via eGift card or use other payment method.', {}, { context: 'egift' }),
      });
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

    // Check if complete purchase button should be active or not
    // pass 'false' to ignore inline error messages.
    const activeClass = this.completePurchaseButtonActive(false)
      ? 'active'
      : 'in-active';

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
      case 'tamara':
        buttonText = Drupal.t('Continue with tamara', {}, { context: 'tamara' });
        break;
      default:
        buttonText = Drupal.t('complete purchase');
    }

    return (
      <div className={`checkout-link complete-purchase complete-purchase-cta fadeInUp notInMobile submit ${activeClass} ${paymentMethod}`} style={{ animationDelay: '0.5s' }}>
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
