import React from 'react';
import ReactDOM from 'react-dom';
import PdpLayout from './pdp-layout/components/pdp-layout';

/* eslint-disable */
(function (Drupal) {
  let pdpInitiated = false;

  Drupal.behaviors.pdpReact = {
    attach: function () {
      initiatePDP();
    }
  };

  function initiatePDP() {
    if (pdpInitiated) {
      return;
    }

    // Do not load if user is not focusing/checking this tab right now.
    if (!document.hasFocus()) {
      return;
    }

    pdpInitiated = true;

    ReactDOM.render(
      <PdpLayout />,
      document.getElementById('pdp-layout'),
    );
  }
}(Drupal));
/* eslint-enable */
