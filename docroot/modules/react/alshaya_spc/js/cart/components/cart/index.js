import React from 'react';
import Cookies from 'js-cookie';
import '../../../utilities/interceptor/interceptor';
import SectionTitle from '../../../utilities/section-title';
import CartItems from '../cart-items';
import CartRecommendedProducts from '../recommended-products';
import MobileCartPreview from '../mobile-cart-preview';
import OrderSummaryBlock from '../../../utilities/order-summary-block';
import CheckoutMessage from '../../../utilities/checkout-message';
import CartPromoBlock from '../cart-promo-block';
import EmptyResult from '../../../utilities/empty-result';
import Loading from '../../../utilities/loading';
import VatFooterText from '../../../utilities/vat-footer';
import { stickyMobileCartPreview } from '../../../utilities/stickyElements/stickyElements';
import { checkCartCustomer } from '../../../utilities/cart_customer_util';
import { smoothScrollTo } from '../../../utilities/smoothScroll';
import { fetchCartData } from '../../../utilities/api/requests';
import DynamicPromotionBanner from '../dynamic-promotion-banner';
import DeliveryInOnlyCity from '../../../utilities/delivery-in-only-city';
import AuraCartContainer from '../../../aura-loyalty/components/aura-cart-rewards/aura-cart-container';
import isAuraEnabled, { isCheckoutTracker } from '../../../../../js/utilities/helper';
import { openFreeGiftModal, selectFreeGiftModal } from '../../../utilities/free_gift_util';
import PostpayCart from '../postpay/postpay';
import Postpay from '../../../utilities/postpay';
import PostpayEligiblityMessage from '../postpay/postpay-eligiblity-message';
import SASessionBanner from '../../../smart-agent-checkout/s-a-session-banner';
import SAShareStrip from '../../../smart-agent-checkout/s-a-share-strip';
import ConditionalView
  from '../../../../../js/utilities/components/conditional-view';
import DeliveryAreaSelect from '../delivery-area-select';
import { getCartShippingMethods } from '../../../utilities/delivery_area_util';
import { removeFullScreenLoader, showFullScreenLoader } from '../../../utilities/checkout_util';
import SelectAreaPanel from '../../../expressdelivery/components/select-area-panel';
import { isExpressDeliveryEnabled, checkAreaAvailabilityStatusOnCart } from '../../../../../js/utilities/expressDeliveryHelper';
import collectionPointsEnabled from '../../../../../js/utilities/pudoAramaxCollection';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';
import Tabby from '../../../../../js/tabby/utilities/tabby';
import TabbyWidget from '../../../../../js/tabby/components';
import { cartContainsOnlyVirtualProduct } from '../../../utilities/egift_util';
import DynamicYieldPlaceholder from '../../../../../js/utilities/components/dynamic-yield-placeholder';
import isHelloMemberEnabled from '../../../../../js/utilities/helloMemberHelper';
import { isUserAuthenticated } from '../../../backend/v2/utility';
import { applyHelloMemberLoyalty } from '../../../hello-member-loyalty/components/hello-member-checkout-rewards/utilities/loyalty_helper';
import { isOnlineReturnsCartBannerEnabled } from '../../../../../js/utilities/onlineReturnsHelper';
import OnlineReturnsCartBanner from '../../../../../alshaya_online_returns/js/cart/online-returns-cart-banner';
import CartPaymentMethodsLogos from '../payment-methods-logos';
import Tamara from '../../../../../js/tamara/utilities/tamara';
import DeliveryPropositions from '../../../delivery-propositions/components/delivery-propositions';

// Lazy load free delivery usp banner component.
const FreeDeliveryUspBanner = React.lazy(() => import('../free-delivery-usp-banner' /* webpackChunkName: "free_delivery_usp" */));

export default class Cart extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      wait: true,
      items: [],
      totals: [],
      recommendedProducts: [],
      totalItems: null,
      amount: null,
      couponCode: null,
      dynamicPromoLabelsCart: null,
      dynamicPromoLabelsProduct: null,
      inStock: true,
      messageType: null,
      message: null,
      cartShippingMethods: null,
      panelContent: null,
      auraDetails: null,
      // To show/hide Area Select option based on SSD/ED availability.
      showAreaAvailabilityStatusOnCart: false,
      // if set to true, execution will check/recheck
      // DeliveryAreaSelect availability on cart page.
      checkShowAreaAvailabilityStatus: true,
      // Flag to not show the dynamic promotions on cart page, if exclusive promo/coupon
      // is applied the i.e. if this exclusive promo is applied on the basket,
      // the flag value will be true, and we don't render the dynamic promos.
      hasExclusiveCoupon: false,
      // Text to show in free delivery usp banner.
      freeShippingText: null,
    };
  }

  componentDidMount() {
    // Listen to `refreshCart` event triggered from `mini-cart/index.js`.
    document.addEventListener('refreshCart', (e) => {
      const data = e.detail.data();
      checkCartCustomer(data);

      if (typeof data === 'undefined'
        || data === null
        || data.cart_id === null
        || data.error !== undefined) {
        const prevState = this.state;
        this.setState({ ...prevState, wait: false });
      } else {
        this.setState(() => ({
          items: data.items,
          totals: data.totals,
          recommendedProducts: data.recommended_products,
          totalItems: data.items_qty,
          amount: data.cart_total,
          wait: false,
          couponCode: data.coupon_code,
          inStock: data.in_stock,
          hasExclusiveCoupon: data.has_exclusive_coupon,
          ...collectionPointsEnabled() && { collectionCharge: data.collection_charge || '' },
          freeShippingText: data.free_shipping_text,
        }));

        // The cart is empty.
        if (data.items.length === 0) {
          this.setState({
            wait: false,
          });
        }

        // We will not trigger window.dynamicPromotion.apply on cart page
        // if exclusive coupon is applied.
        if (data.has_exclusive_coupon !== true) {
          const cartData = fetchCartData();
          if (cartData instanceof Promise) {
            cartData.then((result) => {
              if (typeof result.error === 'undefined') {
                window.dynamicPromotion.apply(result);
                // Set hello member loyalty when no loyalty is set in cart.
                // For registered user, we need to first get customer identifier number.
                if (isHelloMemberEnabled() && isUserAuthenticated()
                  && !hasValue(result.loyalty_type)) {
                  applyHelloMemberLoyalty(result.cart_id);
                }
              }
            });
          }
        }
      }

      // To show the success/error message on cart top.
      const stockErrorMessage = Drupal.getItemFromLocalStorage('stockErrorResponseMessage');
      if (stockErrorMessage) {
        Drupal.removeItemFromLocalStorage('stockErrorResponseMessage');
        this.setState({
          messageType: 'error',
          message: stockErrorMessage,
        });
        Drupal.logJavascriptError('cart-refresh', stockErrorMessage, GTM_CONSTANTS.CART_ERRORS);
      } else if (data.message !== undefined) {
        this.setState({
          messageType: data.message.type,
          message: data.message.message,
        });
        Drupal.logJavascriptError('cart-refresh', data.message.message, GTM_CONSTANTS.CART_ERRORS);
      } else if (data.in_stock === false) {
        const errorMessage = 'Sorry, one or more products in your basket are no longer available. Please review your basket in order to checkout securely.';
        this.setState({
          messageType: 'error',
          message: Drupal.t('Sorry, one or more products in your basket are no longer available. Please review your basket in order to checkout securely.'),
        });
        Drupal.logJavascriptError('cart-refresh', errorMessage, GTM_CONSTANTS.CART_ERRORS);
      } else if (data.message === undefined && data.in_stock) {
        this.setState((prevState) => {
          if (prevState.message === null) return null;
          return {
            messageType: null,
            message: null,
          };
        });
      }

      const { items } = this.state;
      // If Checkout Tracker is enabled and cart is empty hide checkout tracker
      if (isCheckoutTracker()) {
        if (items.length !== 0) {
          document.getElementById('block-checkouttrackerblock').classList.remove('hide-checkout-tracker');
        } else {
          document.getElementById('block-checkouttrackerblock').classList.add('hide-checkout-tracker');
        }
      }

      // Call dynamic-yield spa api for cart context.
      if (typeof window.DY !== 'undefined' && typeof window.DY.API !== 'undefined') {
        window.DY.API('spa', {
          context: {
            type: 'CART',
            data: Object.keys(items),
            lng: drupalSettings.alshaya_spc.lng,
          },
          countAsPageview: false,
        });
      }
      // Event to trigger to Show Delivery Area Select if express delivery enabled.
      // setting checkShowAreaAvailabilityStatus to true will do the recheck for
      // whether to show DeliveryAreaSelect or not on cart page.
      this.setState({
        checkShowAreaAvailabilityStatus: true,
      });
    }, false);

    // Event handles cart message update.
    document.addEventListener('spcCartMessageUpdate', this.handleCartMessageUpdateEvent, false);

    // Event handle for Dynamic Promotion available.
    document.addEventListener('applyDynamicPromotions', this.saveDynamicPromotions, false);
    // Add RCS Event Listner only is RCS module is enabled.
    if (Object.prototype.hasOwnProperty.call(drupalSettings, 'rcsPhSettings')) {
      window.RcsEventManager.addListener('applyDynamicPromotions', this.saveDynamicPromotions);
    }

    // Event to trigger after free gift detail modal open.
    document.addEventListener('openFreeGiftModalEvent', openFreeGiftModal, false);

    // Event to trigger after free gift listing modal open.
    document.addEventListener('selectFreeGiftModalEvent', selectFreeGiftModal, false);

    // Show labels for delivery methods if express delivery enabled.
    if (isExpressDeliveryEnabled()) {
      document.addEventListener('displayShippingMethods', this.displayShippingMethods, false);
    }

    // Display message from cookies.
    const qtyMismatchError = Cookies.get('middleware_payment_error');

    // If 'middleware_payment_error' cookie exists.
    if (qtyMismatchError !== undefined
      && qtyMismatchError !== null
      && qtyMismatchError.length > 0) {
      // Remove 'middleware_payment_error' cookie.
      Cookies.remove('middleware_payment_error');

      const qtyMismatchErrorInfo = JSON.parse(qtyMismatchError);

      // Handle CART_CHECKOUT_QUANTITY_MISMATCH exception.
      if (qtyMismatchErrorInfo.code === 9010) {
        this.updateCartMessage('error', qtyMismatchErrorInfo.message);
      }
    }

    // Event listerner to update any change in cart totals.
    document.addEventListener('updateTotalsInCart', this.handleTotalsUpdateEvent, false);

    // If aura is enabled, add a listner to update aura customer details.
    if (isAuraEnabled()) {
      document.addEventListener('customerDetailsFetched', this.updateAuraDetails, false);
    }
  }

  componentWillUnmount() {
    document.removeEventListener('spcCartMessageUpdate', this.handleCartMessageUpdateEvent, false);
    if (isAuraEnabled()) {
      document.removeEventListener('customerDetailsFetched', this.updateAuraDetails, false);
    }
  }

  // Event listener to update aura details.
  updateAuraDetails = (event) => {
    this.setState({
      auraDetails: { ...event.detail.stateValues },
    });
  };

  // Event listener to update cart totals.
  handleTotalsUpdateEvent = (event) => {
    const { totals } = event.detail;
    this.setState({ totals });
  };

  saveDynamicPromotions = (event) => {
    const {
      cart_labels: cartLabels,
      products_labels: productLabels,
    } = event.detail;

    this.setState({
      dynamicPromoLabelsCart: cartLabels,
      dynamicPromoLabelsProduct: productLabels,
    });

    // Make cart preview sticky.
    stickyMobileCartPreview();
  };

  handleCartMessageUpdateEvent = (event) => {
    const { type, message } = event.detail;
    this.updateCartMessage(type, message);
  };

  updateCartMessage = (actionMessageType, actionMessage) => {
    this.setState({ actionMessageType, actionMessage });
    if (document.getElementsByClassName('spc-messages-container').length > 0) {
      smoothScrollTo('.spc-pre-content');
    }
  };

  preparePostpayMessage = (totals) => {
    let postpay = null;
    let postpayEligibilityMessage = null;
    if (Postpay.isPostpayEnabled() && !Tamara.isTamaraEnabled()) {
      postpay = (
        <PostpayCart
          amount={totals.base_grand_total}
          pageType="cart"
          classNames="spc-postpay-mobile-preview"
          mobileOnly
        />
      );
      postpayEligibilityMessage = (
        <PostpayEligiblityMessage
          text={drupalSettings.alshaya_spc.postpay_eligibility_message}
        />
      );
    }
    return {
      postpay,
      postpayEligibilityMessage,
    };
  }

  displayShippingMethods = (event) => {
    const currentArea = event.detail;
    const { checkShowAreaAvailabilityStatus, items } = this.state;
    // If cart contain only virtual products then we don't check the
    // cart shipping methods.
    if (cartContainsOnlyVirtualProduct({ items })) {
      return;
    }

    showFullScreenLoader();
    // fetch product level SSD/ED status only on initial load
    // or when user removes any product.
    if (checkShowAreaAvailabilityStatus) {
      // check shipping Methods without area to get the
      // Default shipping methods.
      getCartShippingMethods(null).then(
        (response) => {
          if (response !== null) {
            this.setState({
              cartShippingMethods: response,
            });
            // Check if SDD/ED is available on product level.
            if (typeof response !== 'undefined'
              && response !== null
              && !hasValue(response.error)
              && checkAreaAvailabilityStatusOnCart(response)) {
              this.setState({
                showAreaAvailabilityStatusOnCart: true,
              });
              // fetch Area based shipping methods if current area
              // is selected by user.
              if (currentArea !== null) {
                this.setCartShippingMethods(currentArea);
              }
            } else {
              // Don't show DeliveryAreaSelect if no product supports
              // SDD/ED on product level.
              this.setState({
                showAreaAvailabilityStatusOnCart: false,
              });
            }
            // Setting check area availablity to false,
            // to stop product level API call if user only
            // Area change.
            this.setState({
              checkShowAreaAvailabilityStatus: false,
            });
          }
        },
      );
    } else {
      // set Cart shipping methods based on selected area.
      this.setCartShippingMethods(currentArea);
    }
    removeFullScreenLoader();
  }

  setCartShippingMethods = (currentArea) => {
    getCartShippingMethods(currentArea).then(
      (responseWithArea) => {
        if (responseWithArea !== null) {
          this.setState({
            cartShippingMethods: responseWithArea,
          });
        }
      },
    );
  }


  // Adding panel for area list block.
  getPanelData = (data) => {
    // Adds loading class for showing loader on onclick of delivery panel.
    document.querySelector('.delivery-loader').classList.add('loading');
    this.setState({
      panelContent: data,
    });
  };

  // Removing panel for area list block.
  removePanelData = () => {
    this.setState({
      panelContent: null,
    });
  };

  render() {
    const {
      wait,
      items,
      messageType,
      message,
      totalItems,
      totals,
      couponCode,
      inStock,
      actionMessageType,
      actionMessage,
      dynamicPromoLabelsCart,
      dynamicPromoLabelsProduct,
      cartShippingMethods,
      panelContent,
      collectionCharge,
      auraDetails,
      showAreaAvailabilityStatusOnCart,
      hasExclusiveCoupon,
      freeShippingText,
    } = this.state;

    let preContentActive = 'hidden';

    if (wait) {
      return <Loading />;
    }

    if (message !== null || actionMessage !== undefined) {
      preContentActive = 'visible';
    }

    if (dynamicPromoLabelsCart !== null) {
      if (dynamicPromoLabelsCart.qualified.length !== 0
        || Object.keys(dynamicPromoLabelsCart.next_eligible).length !== 0) {
        preContentActive = 'visible';
      }
    }

    if (!wait && items.length === 0) {
      return (
        <>
          <EmptyResult Message={Drupal.t('Your shopping bag is empty.')} />
        </>
      );
    }

    const postPayData = this.preparePostpayMessage(totals);
    if (postPayData.postpayEligibilityMessage !== null) {
      preContentActive = 'visible';
    }

    // Get Smart Agent Info if available.
    const smartAgentInfo = typeof Drupal.smartAgent !== 'undefined'
      ? Drupal.smartAgent.getInfo()
      : false;

    if (smartAgentInfo) {
      preContentActive = 'visible';
    }

    // Show 5K tabby limit widget only when grand total is over cart widget limit config.
    if (Tabby.isTabbyEnabled()
      && totals.base_grand_total > drupalSettings.tabby.cart_widget_limit
    ) {
      preContentActive = 'visible';
    }

    // Get empty divs count for dynamic yield recommendations.
    let cartEmptyDivsCount = 0;
    if (hasValue(drupalSettings.cartDyamicYieldDivsCount)) {
      cartEmptyDivsCount = drupalSettings.cartDyamicYieldDivsCount;
    }

    return (
      <>
        <div className={`spc-pre-content ${preContentActive}`} style={{ animationDelay: '0.4s' }}>
          {/* This will be used for global error message. */}
          <CheckoutMessage type={messageType} context="page-level-cart">
            {message}
          </CheckoutMessage>
          {/* This will be used for any action/event on basket page. */}
          <CheckoutMessage type={actionMessageType} context="page-level-cart-action">
            {actionMessage}
          </CheckoutMessage>
          {/* Displaying dynamic promotion labels only when no exclusive
           coupon gets applied in basket. */}
          {hasExclusiveCoupon !== true
            && (<DynamicPromotionBanner dynamicPromoLabelsCart={dynamicPromoLabelsCart} />)}
          {postPayData.postpayEligibilityMessage}
          {/* Displaying tabby widget only if tabby is enabled and
          tamara is enabled */}
          <ConditionalView condition={Tabby.isTabbyEnabled() && Tabby.showTabbyWidget()}>
            <TabbyWidget
              pageType="cart"
              classNames="spc-tabby-info"
              mobileOnly={false}
              id="tabby-cart-info"
            />
          </ConditionalView>
          <ConditionalView condition={smartAgentInfo !== false}>
            <>
              <SASessionBanner agentName={smartAgentInfo.name} />
              <SAShareStrip />
            </>
          </ConditionalView>
        </div>
        <div className="spc-pre-content-sticky fadeInUp" style={{ animationDelay: '0.4s' }}>
          <MobileCartPreview total_items={totalItems} totals={totals} />
          {postPayData.postpay}
          {/* Displaying tabby widget only if tabby is enabled and
          tamara is disabled */}
          <ConditionalView condition={Tabby.isTabbyEnabled() && Tabby.showTabbyWidget()}>
            <TabbyWidget
              pageType="cart"
              classNames="spc-tabby-mobile-preview"
              mobileOnly
              id="tabby-promo-cart-mobile"
            />
          </ConditionalView>
        </div>
        {hasValue(freeShippingText) && (
          <React.Suspense fallback={<Loading />}>
            <FreeDeliveryUspBanner bannerText={freeShippingText} />
          </React.Suspense>
        )}
        <div className="spc-main">
          <div className="spc-content">
            <div className="spc-title-wrapper">
              <SectionTitle animationDelayValue="0.4s">
                <span>{`${Drupal.t('my shopping bag')} `}</span>
                <span>{Drupal.t('(@qty items)', { '@qty': totalItems })}</span>
              </SectionTitle>
              <ConditionalView condition={isExpressDeliveryEnabled()}>
                <DeliveryAreaSelect
                  animationDelayValue="0.4s"
                  getPanelData={this.getPanelData}
                  removePanelData={this.removePanelData}
                  showAreaAvailabilityStatusOnCart={showAreaAvailabilityStatusOnCart}
                />
              </ConditionalView>
            </div>
            <DeliveryInOnlyCity />
            {isOnlineReturnsCartBannerEnabled() && (
              <OnlineReturnsCartBanner />
            )}
            <CartItems
              dynamicPromoLabelsProduct={dynamicPromoLabelsProduct}
              hasExclusiveCoupon={hasExclusiveCoupon}
              items={items}
              couponCode={couponCode}
              selectFreeGift={this.selectFreeGift}
              totals={totals}
              cartShippingMethods={cartShippingMethods}
            />
          </div>
          <div className="spc-sidebar">
            <CartPromoBlock
              coupon_code={couponCode}
              inStock={inStock}
              dynamicPromoLabelsCart={dynamicPromoLabelsCart}
              items={items}
              totals={totals}
              hasExclusiveCoupon={hasExclusiveCoupon}
            />
            <ConditionalView condition={isAuraEnabled()}>
              <AuraCartContainer totals={totals} auraDetails={auraDetails} />
            </ConditionalView>
            {/* This will be used for the order summary section on cart page,
            where we will show the coupon code on the discount tooltip
            if any exclusive coupon code gets applied. */}
            <OrderSummaryBlock
              totals={totals}
              in_stock={inStock}
              couponCode={couponCode}
              hasExclusiveCoupon={hasExclusiveCoupon}
              show_checkout_button
              animationDelay="0.5s"
              context="cart"
              {...(collectionPointsEnabled()
                && hasValue(collectionCharge)
                && { collectionCharge }
              )}
            />
            {/* Display all available payment methods icons on the cart page
            below the continue to checkout button only if the config
            display_cart_payment_icons is set to true. */}
            {drupalSettings.alshaya_spc.display_cart_payment_icons
              && <CartPaymentMethodsLogos paymentMethods={drupalSettings.payment_methods} />}
            {/* Display all delivery propositions icons/text on the cart page
            below the continue to checkout button. */}
            <DeliveryPropositions />
          </div>
        </div>
        <div className="spc-post-content">
          {drupalSettings.alshaya_spc.display_cart_crosssell
            && <CartRecommendedProducts sectionTitle={Drupal.t('you may also like')} items={items} />}
          <DynamicYieldPlaceholder
            context="cart"
            placeHolderCount={cartEmptyDivsCount}
          />
        </div>
        <div className="spc-footer">
          <VatFooterText />
        </div>
        <ConditionalView condition={isExpressDeliveryEnabled()}>
          <div className="select-area-popup-wrapper">
            <SelectAreaPanel
              panelContent={panelContent}
            />
          </div>
        </ConditionalView>
      </>
    );
  }
}
