import React from 'react';
import SectionTitle from '../section-title';
import TotalLineItems from '../total-line-items';
import CheckoutCartItems from '../../checkout/components/checkout-cart-items';
import {
  validateCartData,
  cartLocalStorageHasSameItems,
  showFullScreenLoader,
  removeFullScreenLoader,
} from '../checkout_util';
import dispatchCustomEvent from '../events';

/**
 * Click handler for `continue checkout`.
 */
const continueCheckout = (e) => {
  e.preventDefault();
  // Show loader.
  showFullScreenLoader();
  const cartData = validateCartData();
  if (cartData instanceof Promise) {
    cartData.then((cartResult) => {
      // Remove loader.
      removeFullScreenLoader();

      let sameNumberOfItems = true;
      // If no error or OOS.
      if (cartResult.error === undefined
        && cartResult.in_stock !== false
        && (cartResult.response_message === null
        || cartResult.response_message.status !== 'error_coupon')) {

        // If storage has same number of items as we get in cart.
        sameNumberOfItems = cartLocalStorageHasSameItems(cartResult);
        if (sameNumberOfItems === true) {
          const continueCheckoutLink = (drupalSettings.user.uid === 0) ?
            'cart/login' :
            'checkout';

          // Redirect to next page.
          //window.location.href = Drupal.url(continueCheckoutLink);
          return;
        }
      }

      // If error/exception, show at cart top.
      if (cartResult.error !== undefined) {
        dispatchCustomEvent('spcCartMessageUpdate', {
          type: 'error',
          message: cartResult.error_message,
        });
        return;
      }

      // If error from invalid coupon.
      if (cartResult.response_message !== null
        && cartResult.response_message.status === 'error_coupon') {
        // Calling 'promo' error event.
        dispatchCustomEvent('spcCartPromoError', {
          message: cartResult.response_message.msg
        });
      }

      // Calling refresh mini cart event so that storage is updated.
      dispatchCustomEvent('refreshMiniCart', {
        data: () => cartResult,
      });

      // Calling refresh cart event so that cart components
      // are refreshed.
      dispatchCustomEvent('refreshCart', {
        data: () => cartResult,
      });

      if (sameNumberOfItems === false) {
        // Dispatch event for error to show.
        dispatchCustomEvent('spcCartMessageUpdate', {
          type: 'error',
          message: Drupal.t('Sorry, one or more products in your basket are no longer available and were removed from your basket.'),
        });
      }

    });
  }
};

const OrderSummaryBlock = (props) => {
  const {
    cart_promo: cartPromo,
    item_qty: itemQty,
    show_checkout_button: showCheckoutButton,
    items,
    totals,
  } = props;
  const promoData = cartPromo;
  let orderSummaryTitle = Drupal.t('Order Summary');
  const continueCheckoutLink = (window.drupalSettings.user.uid === 0) ? 'cart/login' : 'checkout';
  // To be used on checkout page.
  if (itemQty !== undefined) {
    orderSummaryTitle = Drupal.t('order summary (@qty items)', { '@qty': itemQty });
  }

  return (
    <div className="spc-order-summary-block">
      <SectionTitle>{orderSummaryTitle}</SectionTitle>
      {/* To Be used on Checkout Delivery pages. */}
      {!showCheckoutButton
        && (
        <div className={`product-content product-count-${Object.keys(items).length}`}>
          <CheckoutCartItems items={items} />
        </div>
        )}
      <div className="block-content">
        {/* To Be used later on Checkout Delivery pages. */}
        <div className="products" />
        <TotalLineItems totals={totals} cart_promo={promoData} />
        {/* To Be used on cart page only. */}
        {showCheckoutButton
        && (
        <div className="actions">
          <div className="checkout-link submit">
            <a
              onClick={(e) => continueCheckout(e)}
              href={Drupal.url(continueCheckoutLink)}
              className="checkout-link"
            >
              {Drupal.t('continue to checkout')}
            </a>
          </div>
        </div>
        )}
      </div>
    </div>
  );
};

export default OrderSummaryBlock;
