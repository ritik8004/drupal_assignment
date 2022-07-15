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
import collectionPointsEnabled from '../../../../js/utilities/pudoAramaxCollection';
import { hasValue } from '../../../../js/utilities/conditionsUtility';
import dispatchCustomEvent from '../events';

/**
 * Handles OOS for idle cart pages when product is updated in MDC.
 */
const handleOutOfStock = (cartResult) => {
  const response = (cartResult || {}).response_message || {};
  if (response.status === 'json_error' && response.msg.indexOf('out of stock') > -1) {
    const errMsg = 'Sorry, one or more products in your basket are no longer available.';
    // Dispatch event for error to show.
    dispatchCustomEvent('spcCartMessageUpdate', {
      type: 'error',
      message: Drupal.t('@errMsg', { '@errMsg': errMsg }),
    });
    Drupal.logJavascriptError('continue to checkout', errMsg, GTM_CONSTANTS.CART_ERRORS);
    return false;
  }
  return true;
};

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
      handleOutOfStock(cartResult);
    });
  }
};

const OrderSummaryBlock = ({
  item_qty: itemQty,
  show_checkout_button: showCheckoutButton,
  items,
  totals,
  in_stock: inStock,
  animationDelay: animationDelayValue,
  context,
  couponCode,
  collectionCharge,
}) => {
  const orderSummaryTitle = Drupal.t('Order Summary');
  const continueCheckoutLink = (window.drupalSettings.user.uid === 0) ? 'cart/login' : 'checkout';
  // To be used on checkout page.
  const orderSummaryCount = itemQty !== undefined ? Drupal.t('(@qty items)', { '@qty': itemQty }) : '';

  let activeClass = '';
  if (inStock === false) {
    activeClass = 'in-active';
  }

  let elClasses = 'spc-order-summary-block fadeInUp notInMobile';
  let styles = {
    animationDelay: animationDelayValue,
  };
  if (context === 'print') {
    elClasses = 'spc-order-summary-block no-animate';
    styles = {
      animation: 'none !important',
      transition: 'none !important',
    };
  }

  return (
    <div className={elClasses} style={styles}>
      <SectionTitle>
        <span>{orderSummaryTitle}</span>
        <span>{` ${orderSummaryCount}`}</span>
      </SectionTitle>
      {/* To Be used on Checkout Delivery pages. */}
      {!showCheckoutButton
        && (
        <div className={`product-content product-count-${Object.keys(items).length}`}>
          <CheckoutCartItems items={items} couponCode={couponCode} context={context} />
        </div>
        )}
      <div className="block-content">
        {/* To Be used later on Checkout Delivery pages. */}
        <div className="products" />
        <TotalLineItems
          totals={totals}
          isCartPage={showCheckoutButton}
          context={context}
          {...(collectionPointsEnabled()
            && hasValue(collectionCharge)
            && { collectionCharge }
          )}
        />
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
