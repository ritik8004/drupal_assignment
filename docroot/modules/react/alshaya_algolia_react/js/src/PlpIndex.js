import React from 'react';
import ReactDOM from 'react-dom';
import PlpApp from './plp/PlpApp';

/* eslint-disable */
(function (Drupal) {
  let plpAlgoliaInitiated = false;

  Drupal.behaviors.plpAlgolia = {
    attach: function () {
      initiatePlpAlgolia();
    }
  };

  function initiatePlpAlgolia () {
    if (plpAlgoliaInitiated) {
      return;
    }

    // Do not load if user is not focusing/checking this tab right now.
    if (!document.hasFocus()) {
      return;
    }

    plpAlgoliaInitiated = true;

    ReactDOM.render(
      <PlpApp />,
      document.querySelector('#alshaya-algolia-plp'),
    );
  }
}(Drupal));
/* eslint-enable */
