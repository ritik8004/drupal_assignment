import React from 'react';
import ReactDOM from 'react-dom';
import CartNotificationDrawer from './cart-notification-drawer/components/cart-notification-drawer';

const container = document.getElementById('cart_notification');
if (container) {
  ReactDOM.render(
    <CartNotificationDrawer />,
    container,
  );
}
