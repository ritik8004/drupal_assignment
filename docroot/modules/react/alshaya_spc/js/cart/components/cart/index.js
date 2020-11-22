import React from 'react';
import axios from 'axios';

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
    };
  }

  componentDidMount() {
    // Listen to `refreshCart` event triggered from `mini-cart/index.js`.
    document.addEventListener('refreshCart', (e) => {
      const data = e.detail.data();
      checkCartCustomer(data);

      if (typeof data === 'undefined'
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
      if (data.message !== undefined) {
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

      // Call dynamic-yield spa api for cart context.
      const { items } = this.state;
      window.DY.API('spa', {
        context: {
          type: 'CART',
          data: Object.keys(items),
          lng: drupalSettings.alshaya_spc.lng,
        },
        countAsPageview: false,
      });
    }, false);

    // Event handles cart message update.
    document.addEventListener('spcCartMessageUpdate', this.handleCartMessageUpdateEvent, false);

    // Event handle for Dynamic Promotion available.
    document.addEventListener('applyDynamicPromotions', this.saveDynamicPromotions, false);
  }

  componentWillUnmount() {
    document.removeEventListener('spcCartMessageUpdate', this.handleCartMessageUpdateEvent, false);
  }

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

  /**
   * Select and add free gift item.
   */
  selectFreeGift = (codeValue, sku, type, promoType) => {
    if (codeValue !== undefined) {
      // Open free gift modal for collection free gifts.
      if (promoType === 'FREE_GIFT_SUB_TYPE_ONE_SKU') {
        const body = document.querySelector('body');
        body.classList.add('free-gifts-modal-overlay');
        document.getElementById('spc-free-gift').click();
      } else {
        document.getElementById('promo-code').value = codeValue.trim();
        document.getElementById('promo-action-button').click();
        const url = Drupal.url(`rest/v1/product/${sku}`);
        axios.get(url).then((response) => {
          if (response.data.length !== 0) {
            let configurableValues = response.data.configurable_values;
            let variantSku = '';
            if (type === 'configurable') {
              variantSku = response.data.variants[0].sku;
              configurableValues = response.data.variants[0].configurable_values;
            }
            const postData = {
              promo: codeValue,
              sku,
              configurable_values: configurableValues,
              variant: variantSku,
              type,
            };
            axios.post('/middleware/public/select-free-gift', {
              headers: {
                'Content-Type': 'application/json',
              },
              data: JSON.stringify(postData),
            }).then((cartresponse) => {
              if (cartresponse.data.length !== 0) {
                this.setState({
                  items: cartresponse.data.cart.items,
                });
              }
            });
          }
        });
      }
    }
  };

  /**
   * Add class to body and trigger free gift modal.
   */
  openCartFreeGiftModal = (e, coupon, sku, type) => {
    const body = document.querySelector('body');
    body.classList.add('free-gifts-modal-overlay');
    document.getElementById('spc-free-gift').click();
    setTimeout(() => {
      const freeGiftLink = document.getElementById('select-simple-free-gift');
      if (freeGiftLink !== null) {
        freeGiftLink.addEventListener('click', () => {
          this.selectFreeGift(coupon, sku);
          body.classList.remove('free-gifts-modal-overlay');
        });
      }
      const addFreeGiftButton = document.getElementsByClassName('select-free-gift')[1];
      if (addFreeGiftButton.length !== undefined) {
        freeGiftLink.addEventListener('click', (event) => {
          event.preventDefault();
          this.selectFreeGift(coupon, sku, type);
          body.classList.remove('free-gifts-modal-overlay');
        });
      }
    }, 5000);
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
        </div>
        <div className="spc-pre-content-sticky fadeInUp" style={{ animationDelay: '0.4s' }}>
          <MobileCartPreview total_items={totalItems} totals={totals} />
        </div>
        <div className="spc-main">
          <div className="spc-content">
            <SectionTitle animationDelayValue="0.4s">
              <span>{`${Drupal.t('my shopping bag')} `}</span>
              <span>{Drupal.t('(@qty items)', { '@qty': totalItems })}</span>
            </SectionTitle>
            <DeliveryInOnlyCity />
            <CartItems
              dynamicPromoLabelsProduct={dynamicPromoLabelsProduct}
              items={items}
              couponCode={couponCode}
              selectFreeGift={this.selectFreeGift}
              openCartFreeGiftModal={this.openCartFreeGiftModal}
            />
          </div>
          <div className="spc-sidebar">
            <CartPromoBlock
              coupon_code={couponCode}
              inStock={inStock}
              dynamicPromoLabelsCart={dynamicPromoLabelsCart}
              items={items}
            />
            <OrderSummaryBlock
              totals={totals}
              in_stock={inStock}
              show_checkout_button
              animationDelay="0.5s"
              context="cart"
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
      </>
    );
  }
}
