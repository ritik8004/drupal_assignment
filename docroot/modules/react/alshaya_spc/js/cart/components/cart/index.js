import React from 'react';

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
import PromotionsDynamicLabels from '../../../utilities/promotions-dynamic-labels';

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
      cartPromo: null,
      inStock: true,
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
          cartPromo: data.cart_promo,
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

        // Make cart preview sticky.
        stickyMobileCartPreview();

        // Make side bar sticky.
        stickySidebar();

        const cartData = fetchCartData();
        if (cartData instanceof Promise) {
          cartData.then((result) => {
            if (typeof result.error === 'undefined') {
              PromotionsDynamicLabels.apply(result);
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
      }
    }, false);

    // Event handles cart message update.
    document.addEventListener('spcCartMessageUpdate', this.handleCartMessageUpdateEvent, false);
  }

  componentWillUnmount() {
    document.removeEventListener('spcCartMessageUpdate', this.handleCartMessageUpdateEvent, false);
  }

  handleCartMessageUpdateEvent = (event) => {
    const { type, message } = event.detail;
    this.updateCartMessage(type, message);
  };

  updateCartMessage = (actionMessageType, actionMessage) => {
    this.setState({ actionMessageType, actionMessage });
    if (document.getElementsByClassName('spc-messages-container').length > 0) {
      smoothScrollTo('.spc-messages-container');
    }
  };

  render() {
    const {
      wait,
      items,
      recommendedProducts,
      messageType,
      message,
      totalItems,
      totals,
      couponCode,
      inStock,
      cartPromo,
      actionMessageType,
      actionMessage,
    } = this.state;

    if (wait) {
      return <Loading />;
    }

    if (!wait && items.length === 0) {
      return (
        <>
          <EmptyResult Message={Drupal.t('Your shopping bag is empty.')} />
          <CartRecommendedProducts sectionTitle={Drupal.t('new arrivals')} recommended_products={recommendedProducts} />
          <CartRecommendedProducts sectionTitle={Drupal.t('trending now')} recommended_products={recommendedProducts} />
        </>
      );
    }

    return (
      <>
        <div className="spc-pre-content">
          {/* This will be used for global error message. */}
          <CheckoutMessage type={messageType} context="page-level-cart">
            {message}
          </CheckoutMessage>

          <div id="spc-cart-promotion-dynamic-message-qualified" />
          <div id="spc-cart-promotion-dynamic-message-next-eligible" />

          {/* This will be used for any action/event on basket page. */}
          <CheckoutMessage type={actionMessageType} context="page-level-cart-action">
            {actionMessage}
          </CheckoutMessage>

          <MobileCartPreview total_items={totalItems} totals={totals} />
        </div>
        <div className="spc-main">
          <div className="spc-content">
            <SectionTitle>
              {Drupal.t('my shopping bag (@qty items)', { '@qty': totalItems })}
            </SectionTitle>
            <CartItems items={items} />
          </div>
          <div className="spc-sidebar">
            <CartPromoBlock coupon_code={couponCode} inStock={inStock} />
            <OrderSummaryBlock
              totals={totals}
              in_stock={inStock}
              cart_promo={cartPromo}
              show_checkout_button
            />
          </div>
        </div>
        <div className="spc-post-content">
          <CartRecommendedProducts sectionTitle={Drupal.t('you may also like')} recommended_products={recommendedProducts} />
        </div>
        <div className="spc-footer">
          <VatFooterText />
        </div>
      </>
    );
  }
}
