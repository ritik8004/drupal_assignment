import React from 'react';

import '../../../utilities/interceptor/interceptor';
import ClicknCollectContextProvider from '../../../context/ClicknCollect';
import { fetchCartDataForCheckout } from '../../../utilities/api/requests';
import Loading from '../../../utilities/loading';
import OrderSummaryBlock from '../../../utilities/order-summary-block';
import HDBillingAddress from '../hd-billing-address';
import CnCBillingAddress from '../cnc-billing-address';
import CompletePurchase from '../complete-purchase';
import DeliveryInformation from '../delivery-information';
import DeliveryMethods from '../delivery-methods';
import PaymentMethods from '../payment-methods';
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
import SASessionBanner from '../../../smart-agent-checkout/s-a-session-banner';
import SAShareStrip from '../../../smart-agent-checkout/s-a-share-strip';
import collectionPointsEnabled from '../../../../../js/utilities/pudoAramaxCollection';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';
import { getCartShippingMethods } from '../../../utilities/delivery_area_util';
import { checkAreaAvailabilityStatusOnCart, isExpressDeliveryEnabled } from '../../../../../js/utilities/expressDeliveryHelper';
import RedeemEgiftCard from '../../../egift-card';
import { cartContainsAnyNormalProduct, cartContainsOnlyVirtualProduct } from '../../../utilities/egift_util';
import { isEgiftCardEnabled } from '../../../../../js/utilities/util';
import isHelloMemberEnabled from '../../../../../js/utilities/helloMemberHelper';
import HelloMemberCheckoutContainer from '../../../hello-member-loyalty/components/hello-member-checkout-rewards/hello-member-checkout-container';

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
      isExpressDeliveryAvailable: false,
      // shippingInfoUpdated is used to maintain flag when shipping information updated in cart.
      // it can be used in component props.
      shippingInfoUpdated: false,
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

          // Check if SSD/ED is enabled.
          // If cart contain only virtual products then we don't check the
          // cart shipping methods.
          if (isExpressDeliveryEnabled() && !cartContainsOnlyVirtualProduct(result)) {
            try {
              const cartId = result.cart_id_int;
              if (cartId) {
                // Get shipping Methods on product level.
                getCartShippingMethods(null, null, cartId).then(
                  (response) => {
                    if (response !== null) {
                      // Show prefilled area when SDD/ED is available.
                      if (!hasValue(response.error)
                        && checkAreaAvailabilityStatusOnCart(response)) {
                        this.setState({
                          isExpressDeliveryAvailable: true,
                        });
                      }
                      this.processCheckout(result);
                    }
                  },
                );
              }
            } catch (error) {
              Drupal.logJavascriptError('Could not fetch shipping methods', error, GTM_CONSTANTS.CHECKOUT_ERRORS);
            }
          } else {
            this.processCheckout(result);
          }
        });
      } else {
        redirectToCart();
      }
    } catch (error) {
      // In case of error, do nothing.
      Drupal.logJavascriptError('checkout', error, GTM_CONSTANTS.CHECKOUT_ERRORS);
    }

    document.addEventListener('spcCheckoutMessageUpdate', this.handleMessageUpdateEvent, false);
    document.addEventListener('alshayaPostpayInit', () => {
      this.setState({ isPostpayInitialised: true });
    });
    // Listen to the event which is dispatch from addShippingInfo (v2/checkout.js).
    // This event listen on every shipping address update during cart update.
    document.addEventListener('onAddShippingInfoUpdate', this.onAddShippingInfoUpdate, false);
  }

  componentWillUnmount() {
    document.removeEventListener('spcCheckoutMessageUpdate', this.handleMessageUpdateEvent, false);
    document.removeEventListener('onAddShippingInfoUpdate', this.onAddShippingInfoUpdate, false);
  }

  // Set shippingInfoUpdated to true on shipping address update in cart.
  onAddShippingInfoUpdate = () => {
    this.setState({ shippingInfoUpdated: true });
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
        message: drupalSettings.globalErrorMessage,
      });
    }

    // Get promo info.
    if (typeof result.error === 'undefined') {
      window.dynamicPromotion.apply(result);
    }
  }

  processAddressFromLocalStorage = (result) => {
    // Do this only for guests.
    if (!(drupalSettings.userDetails.customerId)) {
      return;
    }

    if (result.shipping && result.shipping.method === null) {
      const shippingAddress = Drupal.getItemFromLocalStorage('shippingaddress-formdata');
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
    let statusContent = message || '';
    const statusType = type || '';
    let errorResponse = '';

    if (statusType === 'error') {
      try {
        errorResponse = JSON.parse(statusContent);
        statusContent = errorResponse.system_error;
      } catch (e) {
        Drupal.logJavascriptError('Unable to parse the error message.', e);
      }
    }

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
    cartData.cart.totals = { ...totals };

    this.setState({ cart: cartData });
  };

  render() {
    const {
      wait,
      cart,
      errorSuccessMessage,
      messageType,
      isPostpayInitialised,
      isExpressDeliveryAvailable,
      shippingInfoUpdated,
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

    // Main wrapper class.
    const mainWrapperClass = `spc-main ${isEgiftCardEnabled()
      && cartContainsOnlyVirtualProduct(cart.cart) ? 'spc-virtual-product-checkout' : ''}`;

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
        <div className={mainWrapperClass}>
          <div className="spc-content">
            {errorSuccessMessage !== null
              && (
              <CheckoutMessage type={messageType} context="page-level-checkout">
                {errorSuccessMessage}
              </CheckoutMessage>
              )}
            <ConditionalView condition={cartContainsAnyNormalProduct(cart.cart)}>
              <DeliveryMethods cart={cart} refreshCart={this.refreshCart} />
              <ClicknCollectContextProvider cart={cart}>
                <DeliveryInformation
                  shippingInfoUpdated={shippingInfoUpdated}
                  refreshCart={this.refreshCart}
                  cart={cart}
                  isExpressDeliveryAvailable={isExpressDeliveryAvailable}
                />
              </ClicknCollectContextProvider>
            </ConditionalView>

            <PaymentMethods
              ref={this.paymentMethods}
              refreshCart={this.refreshCart}
              cart={cart}
              isPostpayInitialised={isPostpayInitialised}
            />

            <ConditionalView condition={isEgiftCardEnabled()}>
              <RedeemEgiftCard
                cart={cart}
                refreshCart={this.refreshCart}
              />
            </ConditionalView>

            {isHelloMemberEnabled() && (
              <HelloMemberCheckoutContainer
                cart={cart}
                refreshCart={this.refreshCart}
              />
            )}

            <ConditionalView condition={isAuraEnabled()}>
              <AuraCheckoutContainer cart={cart} />
            </ConditionalView>

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
              hasExclusiveCoupon={cart.cart.has_exclusive_coupon}
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
