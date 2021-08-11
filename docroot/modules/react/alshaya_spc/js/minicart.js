import React from 'react';
import ReactDOM from 'react-dom';
import MiniCart from './minicart/components/minicart';

/* eslint-disable */
(function (Drupal) {
  let miniCartInitiated = false;

  Drupal.behaviors.spcMiniCart = {
    attach: function () {
      initiateMiniCart();
    }
  };

  function initiateMiniCart() {
    if (miniCartInitiated) {
      return;
    }

    // Do not load if user is not focusing/checking this tab right now.
    if (!document.hasFocus()) {
      return;
    }

    miniCartInitiated = true;

    ReactDOM.render(
      <MiniCart />,
      document.getElementById('mini-cart-wrapper'),
    );
  }
}(Drupal));
/* eslint-enable */
