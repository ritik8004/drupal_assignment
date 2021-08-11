import React from 'react';
import ReactDOM from 'react-dom';
import Cart from './cart/components/cart';

/* eslint-disable */
(function (Drupal) {
  let cartInitiated = false;

  Drupal.behaviors.spcCart = {
    attach: function () {
      initiateCart();
    }
  };

  function initiateCart() {
    if (cartInitiated) {
      return;
    }

    // Do not load if user is not focusing/checking this tab right now.
    if (!document.hasFocus()) {
      return;
    }

    cartInitiated = true;

    ReactDOM.render(
      <Cart />,
      document.getElementById('spc-cart'),
    );
  }
}(Drupal));
/* eslint-enable */
