import React from 'react';
import SectionTitle from '../section-title';
import TotalLineItems from '../total-line-items';
import CheckoutCartItems from '../../checkout/components/checkout-cart-items';
import {
  validateCartData,
  cartValidationOnUpdate,
  showFullScreenLoader,
  removeFullScreenLoader,
} from '../checkout_util';

/**
 * Click handler for `continue checkout`.
 */
const continueCheckout = (e, inStock) => {
  e.preventDefault();

  if (inStock === false) {
    return;
  }

  // Show loader.
  showFullScreenLoader();
  const cartData = validateCartData();
  if (cartData instanceof Promise) {
    cartData.then((cartResult) => {
      // Remove loader.
      removeFullScreenLoader();
      cartValidationOnUpdate(cartResult, true);
    });
  }
};

const OrderSummaryBlock = (props) => {
  const {
    item_qty: itemQty,
    show_checkout_button: showCheckoutButton,
    items,
    totals,
    in_stock: inStock,
    animationDelay: animationDelayValue,
    context,
  } = props;
  const orderSummaryTitle = Drupal.t('Order Summary');
  const continueCheckoutLink = (window.drupalSettings.user.uid === 0) ? 'cart/login' : 'checkout';
  // To be used on checkout page.
  const orderSummaryCount = itemQty !== undefined ? Drupal.t('(@qty items)', { '@qty': itemQty }) : '';

  let activeClass = '';
  if (inStock === false) {
    activeClass = 'in-active';
  }

  return (
    <div className="spc-order-summary-block fadeInUp notInMobile" style={{ animationDelay: animationDelayValue }}>
      <SectionTitle>
        <span>{orderSummaryTitle}</span>
        <span>{` ${orderSummaryCount}`}</span>
      </SectionTitle>
      {/* To Be used on Checkout Delivery pages. */}
      {!showCheckoutButton
        && (
        <div className={`product-content product-count-${Object.keys(items).length}`}>
          <CheckoutCartItems items={items} context={context} />
        </div>
        )}
      <div className="block-content">
        {/* To Be used later on Checkout Delivery pages. */}
        <div className="products" />
        <TotalLineItems totals={totals} />
        {/* To Be used on cart page only. */}
        {showCheckoutButton
        && (
        <div className="actions">
          <div className={`checkout-link submit ${activeClass}`}>
            <a
              onClick={(e) => continueCheckout(e, inStock)}
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
