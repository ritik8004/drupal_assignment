import React from 'react';
import ReactDOM from 'react-dom';
import PdpLayout from './pdp-layout/components/pdp-layout';

// eslint-disable-next-line func-names
(function (Drupal) {
  let pdpInitiated = false;

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

  // eslint-disable-next-line no-param-reassign
  Drupal.behaviors.pdpReact = {
    attach() {
      initiatePDP();
    },
  };
}(Drupal));
