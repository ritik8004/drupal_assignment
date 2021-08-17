import React from 'react';
import ReactDOM from 'react-dom';
import Cart from './cart/components/cart';

// Store the page type for conditions later.
window.spcPageType = 'cart';

// eslint-disable-next-line func-names
(function (Drupal) {
  let cartInitiated = false;

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

  // eslint-disable-next-line no-param-reassign
  Drupal.behaviors.spcCart = {
    attach() {
      initiateCart();
    },
  };
}(Drupal));
