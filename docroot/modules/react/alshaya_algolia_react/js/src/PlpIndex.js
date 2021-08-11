import React from 'react';
import ReactDOM from 'react-dom';
import PlpApp from './plp/PlpApp';

// eslint-disable-next-line func-names
(function (Drupal) {
  let plpAlgoliaInitiated = false;

  function initiatePlpAlgolia() {
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

  // eslint-disable-next-line no-param-reassign
  Drupal.behaviors.plpAlgolia = {
    attach() {
      initiatePlpAlgolia();
    },
  };
}(Drupal));
