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
import { stickyMobileCartPreview, stickySidebar } from '../../../utilities/stickyElements/stickyElements';
import { checkCartCustomer } from '../../../utilities/cart_customer_util';
import { smoothScrollTo } from '../../../utilities/smoothScroll';
import { fetchCartData } from '../../../utilities/api/requests';
import PromotionsDynamicLabelsUtil from '../../../utilities/promotions-dynamic-labels-utility';
import DynamicPromotionBanner from '../dynamic-promotion-banner';
import DeliveryInOnlyCity from '../../../utilities/delivery-in-only-city';
import AuraCartContainer from '../../../aura-loyalty/components/aura-cart-rewards/aura-cart-container';
import isAuraEnabled from '../../../../../js/utilities/helper';
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
import { isExpressDeliveryEnabled } from '../../../../../js/utilities/expressDeliveryHelper';
import collectionPointsEnabled from '../../../../../js/utilities/pudoAramaxCollection';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';
import Tabby from '../../../../../js/tabby/utilities/tabby';
import TabbyWidget from '../../../../../js/tabby/components';

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
          ...collectionPointsEnabled() && { collectionCharge: data.collection_charge || '' },
        }));

        // The cart is empty.
        if (data.items.length === 0) {
          this.setState({
            wait: false,
          });
        }

        // Make side bar sticky.
        stickySidebar();

        const cartData = fetchCartData();
        if (cartData instanceof Promise) {
          cartData.then((result) => {
            if (typeof result.error === 'undefined') {
              PromotionsDynamicLabelsUtil.apply(result);
            }
          });
        }
      }

      // To show the success/error message on cart top.
      const stockErrorMessage = localStorage.getItem('stockErrorResponseMessage');
      if (stockErrorMessage) {
        localStorage.removeItem('stockErrorResponseMessage');
        this.setState({
          messageType: 'error',
          message: stockErrorMessage,
        });
      } else if (data.message !== undefined) {
        this.setState({
          messageType: data.message.type,
          message: data.message.message,
        });
      } else if (data.in_stock === false) {
        this.setState({
          messageType: 'error',
          message: Drupal.t('Sorry, one or more products in your basket are no longer available. Please review your basket in order to checkout securely.'),
        });
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
    }, false);

    // Event handles cart message update.
    document.addEventListener('spcCartMessageUpdate', this.handleCartMessageUpdateEvent, false);

    // Event handle for Dynamic Promotion available.
    document.addEventListener('applyDynamicPromotions', this.saveDynamicPromotions, false);

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
  }

  componentWillUnmount() {
    document.removeEventListener('spcCartMessageUpdate', this.handleCartMessageUpdateEvent, false);
  }

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
    if (Postpay.isPostpayEnabled()) {
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
    showFullScreenLoader();
    getCartShippingMethods(currentArea).then(
      (response) => {
        if (response !== null) {
          this.setState({
            cartShippingMethods: response,
          });
        }
        removeFullScreenLoader();
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
        || dynamicPromoLabelsCart.next_eligible.length !== 0) {
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

    // Check if the tabby is enabled.
    if (Tabby.isTabbyEnabled()) {
      preContentActive = 'visible';
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
          {/* This will be used for Dynamic promotion labels. */}
          <DynamicPromotionBanner dynamicPromoLabelsCart={dynamicPromoLabelsCart} />
          {postPayData.postpayEligibilityMessage}
          <ConditionalView condition={Tabby.isTabbyEnabled()}>
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
          <ConditionalView condition={Tabby.isTabbyEnabled()}>
            <TabbyWidget
              pageType="cart"
              classNames="spc-tabby-mobile-preview"
              mobileOnly
              id="tabby-promo-cart-mobile"
            />
          </ConditionalView>
        </div>
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
                />
              </ConditionalView>
            </div>
            <DeliveryInOnlyCity />
            <CartItems
              dynamicPromoLabelsProduct={dynamicPromoLabelsProduct}
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
            />
            <ConditionalView condition={isAuraEnabled()}>
              <AuraCartContainer totals={totals} items={items} />
            </ConditionalView>
            <OrderSummaryBlock
              totals={totals}
              in_stock={inStock}
              show_checkout_button
              animationDelay="0.5s"
              context="cart"
              {...(collectionPointsEnabled()
                && hasValue(collectionCharge)
                && { collectionCharge }
              )}
            />
          </div>
        </div>
        <div className="spc-post-content">
          {drupalSettings.alshaya_spc.display_cart_crosssell
            && <CartRecommendedProducts sectionTitle={Drupal.t('you may also like')} items={items} />}
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
