import React from 'react';
import ReactDOM from 'react-dom';
import SearchApp from './search/SearchApp';

// Start instant search only after Document ready.
// eslint-disable-next-line func-names
(function ($, drupalSettings, Drupal) {
  let searchInitiated = false;
  function initiateSearch() {
    if (searchInitiated) {
      return;
    }
    // Do not load if user is not focusing/checking this tab right now.
    if (!document.hasFocus()) {
      return;
    }
    searchInitiated = true;
    ReactDOM.render(
      <SearchApp />,
      document.querySelector('#alshaya-algolia-autocomplete'),
    );

    // Attach event to activate search.
    $('#alshaya-algolia-autocomplete input[type="search"]').on('mousedown tap focus', () => {
      window.algoliaSearchActivityStarted = true;
    });
  }

  // We will trigger search only after activity is started.
  window.algoliaSearchActivityStarted = (typeof drupalSettings.algoliaSearch.showSearchResults === 'undefined')
    ? false
    : drupalSettings.algoliaSearch.showSearchResults;

  // eslint-disable-next-line no-param-reassign
  Drupal.behaviors.searchAlgolia = {
    attach() {
      initiateSearch();
    },
  };
}(jQuery, drupalSettings, Drupal));
