import React from 'react';
import ReactDOM from 'react-dom';
import MiniCart from './minicart/components/minicart';

// eslint-disable-next-line func-names
(function (Drupal) {
  let miniCartInitiated = false;

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

  // eslint-disable-next-line no-param-reassign
  Drupal.behaviors.spcMiniCart = {
    attach() {
      initiateMiniCart();
    },
  };
}(Drupal));
