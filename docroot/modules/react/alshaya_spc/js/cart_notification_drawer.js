import React from 'react';
import ReactDOM from 'react-dom';
import isCartNotificationDrawerEnabled from '../../js/utilities/cartNotificationHelper';
import { hasValue } from '../../js/utilities/conditionsUtility';
import CartNotificationDrawer from './cart-notification-drawer/components/cart-notification-drawer';

/**
 * Render cart notification drawer.
 */
const renderCartNotificationDrawer = (e) => {
  if (hasValue(e.detail)) {
    document.querySelector('body').classList.add('overlay-cart-drawer');
    const container = document.getElementById('cart_notification');
    if (container) {
      ReactDOM.render(
        <CartNotificationDrawer productData={e.detail} />,
        container,
      );
    }
  }
};

// Only if cart notification drawer feature is enabled.
if (isCartNotificationDrawerEnabled()) {
  // After product is successfully added to basket,
  // We show cart notification in side drawer.
  document.addEventListener('showCartNotificationDrawer', renderCartNotificationDrawer, false);
}
