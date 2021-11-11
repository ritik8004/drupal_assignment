import React from 'react';

import '../../../utilities/interceptor/interceptor';
import ClicknCollectContextProvider from '../../../context/ClicknCollect';
import { fetchCartDataForCheckout } from '../../../utilities/api/requests';
import Loading from '../../../utilities/loading';
import OrderSummaryBlock from '../../../utilities/order-summary-block';
import HDBillingAddress from '../hd-billing-address';
import CnCBillingAddress from '../cnc-billing-address';
import { stickySidebar } from '../../../utilities/stickyElements/stickyElements';
import CompletePurchase from '../complete-purchase';
import DeliveryInformation from '../delivery-information';
import DeliveryMethods from '../delivery-methods';
import PaymentMethods from '../payment-methods';
import PromotionsDynamicLabelsUtil from '../../../utilities/promotions-dynamic-labels-utility';
import CheckoutMessage from '../../../utilities/checkout-message';
import TermsConditions from '../terms-conditions';
import {
  removeFullScreenLoader,
  isCnCEnabled,
  removeBillingFlagFromStorage,
  addShippingInCart,
  showFullScreenLoader,
} from '../../../utilities/checkout_util';
import ConditionalView from '../../../common/components/conditional-view';
import { smoothScrollTo } from '../../../utilities/smoothScroll';
import VatFooterText from '../../../utilities/vat-footer';
import { redirectToCart } from '../../../utilities/get_cart';
import dispatchCustomEvent from '../../../utilities/events';
import AuraCheckoutContainer from '../../../aura-loyalty/components/aura-checkout-rewards/aura-checkout-container';
import isAuraEnabled from '../../../../../js/utilities/helper';
import validateCartResponse from '../../../utilities/validation_util';
import { getStorageInfo } from '../../../utilities/storage';
import SASessionBanner from '../../../smart-agent-checkout/s-a-session-banner';
import SAShareStrip from '../../../smart-agent-checkout/s-a-share-strip';
import collectionPointsEnabled from '../../../../../js/utilities/pudoAramaxCollection';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';

window.fetchStore = 'idle';

export default class Checkout extends React.Component {
  constructor(props) {
    super(props);

    this.paymentMethods = React.createRef();

    this.state = {
      wait: true,
      cart: null,
      messageType: null,
      errorSuccessMessage: null,
      isPostpayInitialised: false,
    };
  }

  componentDidMount() {
    try {
      // Fetch cart data.
      const cartData = fetchCartDataForCheckout();
      if (cartData instanceof Promise) {
        cartData.then((result) => {
          if (!validateCartResponse(result)) {
            redirectToCart();
            return;
          }

          if (result === undefined
            || result === null) {
            redirectToCart();
            return;
          }

          // Redirect to basket if uid don't match, we will handle
          // association and everything there.
          if (result.uid !== drupalSettings.user.uid) {
            redirectToCart();
            return;
          }
          // Event listerner to update any change in totals in cart object.
          document.addEventListener('updateTotalsInCart', this.handleTotalsUpdateEvent, false);

          this.processAddressFromLocalStorage(result);
          this.processCheckout(result);
        });
      } else {
        redirectToCart();
      }
    } catch (error) {
      // In case of error, do nothing.
      Drupal.logJavascriptError('checkout', error, GTM_CONSTANTS.CHECKOUT_ERRORS);
    }

    // Make sidebar sticky.
    stickySidebar();

    document.addEventListener('spcCheckoutMessageUpdate', this.handleMessageUpdateEvent, false);
    document.addEventListener('alshayaPostpayInit', () => {
      this.setState({ isPostpayInitialised: true });
    });
  }

  componentWillUnmount() {
    document.removeEventListener('spcCheckoutMessageUpdate', this.handleMessageUpdateEvent, false);
  }

  processCheckout = (result) => {
    const cart = result;

    // No further processing required if shipping is not set yet in cart.
    if (typeof result.shipping === 'undefined') {
      return;
    }

    // Set default as user selection for handling conditions.
    cart.delivery_type = result.shipping.type;

    // If CnC is not available and cart has CnC method selected.
    if (cart.shipping.type === 'click_and_collect' && !isCnCEnabled(result)) {
      cart.delivery_type = 'home_delivery';
    }
    // Process Removal of "same as billing" flag before we update the
    // cart state. before the billing address component mounted. as
    // setState will immediately mount the components before localStorage
    // update happens.
    removeBillingFlagFromStorage({ cart });
    dispatchCustomEvent('checkoutCartUpdate', { cart });
    this.setState({
      wait: false,
      cart: { cart },
    });

    // If cart from stale cache.
    if (cart.stale_cart !== undefined && cart.stale_cart === true) {
      dispatchCustomEvent('spcCheckoutMessageUpdate', {
        type: 'error',
        message: drupalSettings.global_error_message,
      });
    }

    // Get promo info.
    if (typeof result.error === 'undefined') {
      PromotionsDynamicLabelsUtil.apply(result);
    }
  }

  processAddressFromLocalStorage = (result) => {
    // Do this only for guests.
    if (!(drupalSettings.userDetails.customerId)) {
      return;
    }

    if (result.shipping && result.shipping.method === null) {
      const shippingAddress = getStorageInfo('shippingaddress-formdata');
      if (shippingAddress) {
        showFullScreenLoader();
        const cartInfo = addShippingInCart('update shipping', shippingAddress);
        if (cartInfo instanceof Promise) {
          cartInfo.then((cartResult) => {
            if (!(cartResult)) {
              return;
            }
            this.processCheckout(cartResult);
            // Remove the loader.
            removeFullScreenLoader();
          });
        }
      }
    }
  }

  handleMessageUpdateEvent = (event) => {
    const { type, message } = event.detail;
    this.updateCheckoutMessage(type, message);
  };

  /**
   * Set the type and message in state to be shown to the user.
   *
   * @param {string} type
   *   The type of the message, will be added as class on selector.
   *
   * @param {string} message
   *   The message to be displayed to the user.
   */
  updateCheckoutMessage = (type, message) => {
    const statusType = type || '';
    const statusContent = message || '';

    this.setState({ messageType: statusType, errorSuccessMessage: statusContent });
    // Checking length as if no type, means no error.
    if ((statusType.length > 0) && (document.getElementsByClassName('spc-content').length > 0)) {
      smoothScrollTo('.spc-content');
    }
  };

  /**
   * Update the cart in storage.
   */
  refreshCart = (cart) => {
    // Remove loader.
    removeFullScreenLoader();

    // If there is error on cart update.
    if (cart.error_message !== undefined) {
      this.updateCheckoutMessage('error', cart.error_message);
      return;
    }

    let redirectToBasketOnError = false;
    // If OOS error, redirect to basket page.
    if (cart.cart.response_message !== null
      && cart.cart.response_message.status === 'json_error') {
      redirectToBasketOnError = true;
    }

    // If need to redirect to basket page.
    if (redirectToBasketOnError === true) {
      redirectToCart();
      return;
    }

    // Reset error message.
    const { messageType, errorSuccessMessage } = this.state;
    if (messageType !== null || errorSuccessMessage !== null) {
      this.updateCheckoutMessage(null, null);
    }
    this.setState({ cart });
  };

  validateBeforePlaceOrder = () => this.paymentMethods.current.validateBeforePlaceOrder();

  /**
   * Get the billing address component for rendering.
   */
  getBillingComponent = () => {
    const { cart } = this.state;

    if (cart.cart.shipping.type === 'home_delivery'
      || (cart.delivery_type !== undefined && cart.delivery_type === 'home_delivery')) {
      return (
        <HDBillingAddress
          refreshCart={this.refreshCart}
          cart={cart}
        />
      );
    }

    return (
      <CnCBillingAddress
        refreshCart={this.refreshCart}
        cart={cart}
      />
    );
  };

  // Event listener to update totals in cart.
  handleTotalsUpdateEvent = (event) => {
    const { cart } = this.state;
    const { totals } = event.detail;
    const cartData = cart;
    cartData.cart.totals = { ...cartData.cart.totals, ...totals };

    this.setState({ cart: cartData });
  };

  render() {
    const {
      wait,
      cart,
      errorSuccessMessage,
      messageType,
      isPostpayInitialised,
    } = this.state;
    // While page loads and all info available.

    if (wait) {
      return <Loading />;
    }

    // If cart not available.
    if (cart === null) {
      return redirectToCart();
    }

    const termConditions = <TermsConditions />;
    const billingComponent = this.getBillingComponent();

    // Get Smart Agent Info if available.
    const smartAgentInfo = typeof Drupal.smartAgent !== 'undefined'
      ? Drupal.smartAgent.getInfo()
      : false;

    return (
      <>
        <div className="spc-pre-content">
          <ConditionalView condition={smartAgentInfo !== false}>
            <>
              <SASessionBanner agentName={smartAgentInfo.name} />
              <SAShareStrip />
            </>
          </ConditionalView>
        </div>
        <div className="spc-main">
          <div className="spc-content">
            {errorSuccessMessage !== null
              && (
              <CheckoutMessage type={messageType} context="page-level-checkout">
                {errorSuccessMessage}
              </CheckoutMessage>
              )}

            <DeliveryMethods cart={cart} refreshCart={this.refreshCart} />
            <ClicknCollectContextProvider cart={cart}>
              <DeliveryInformation refreshCart={this.refreshCart} cart={cart} />
            </ClicknCollectContextProvider>

            {isAuraEnabled()
              ? (
                <AuraCheckoutContainer
                  cart={cart}
                />
              )
              : null}

            <PaymentMethods
              ref={this.paymentMethods}
              refreshCart={this.refreshCart}
              cart={cart}
              isPostpayInitialised={isPostpayInitialised}
            />

            {billingComponent}

            <ConditionalView condition={window.innerWidth > 767}>
              {termConditions}
            </ConditionalView>

            <ConditionalView condition={window.innerWidth > 767}>
              <CompletePurchase
                cart={cart}
                validateBeforePlaceOrder={this.validateBeforePlaceOrder}
              />
            </ConditionalView>

          </div>
          <div className="spc-sidebar">
            <OrderSummaryBlock
              item_qty={cart.cart.items_qty}
              items={cart.cart.items}
              totals={cart.cart.totals}
              in_stock={cart.cart.in_stock}
              cart_promo={cart.cart.cart_promo}
              couponCode={cart.cart.coupon_code}
              show_checkout_button={false}
              animationDelay="0.4s"
              context="checkout"
              {...(collectionPointsEnabled()
                && hasValue(cart.cart.shipping.price_amount)
                && { collectionCharge: cart.cart.shipping.price_amount }
              )}
            />
          </div>
        </div>
        <div className="spc-post-content">
          <ConditionalView condition={window.innerWidth < 768}>
            {termConditions}
          </ConditionalView>
        </div>

        <ConditionalView condition={window.innerWidth < 768}>
          <CompletePurchase
            cart={cart}
            validateBeforePlaceOrder={this.validateBeforePlaceOrder}
          />
        </ConditionalView>

        <div className="spc-footer">
          <VatFooterText />
        </div>
      </>
    );
  }
}
