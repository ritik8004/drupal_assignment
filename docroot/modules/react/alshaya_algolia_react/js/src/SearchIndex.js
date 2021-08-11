import React from 'react';
import ReactDOM from 'react-dom';
import SearchApp from './search/SearchApp';

/* eslint-disable */
(function (Drupal) {
  let searchAlgoliaInitiated = false;

  Drupal.behaviors.searchAlgolia = {
    attach: function () {
      initiateSearchAlgolia();
    }
  };

  function initiateSearchAlgolia() {
    if (searchAlgoliaInitiated) {
      return;
    }

    // Do not load if user is not focusing/checking this tab right now.
    if (!document.hasFocus()) {
      return;
    }

    searchAlgoliaInitiated = true;

    ReactDOM.render(
      <SearchApp />,
      document.querySelector('#alshaya-algolia-autocomplete'),
    );
  }
}(Drupal));
/* eslint-enable */
