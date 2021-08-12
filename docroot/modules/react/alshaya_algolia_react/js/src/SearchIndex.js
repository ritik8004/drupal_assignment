import React from 'react';
import ReactDOM from 'react-dom';
import SearchApp from './search/SearchApp';

// Start instant search only after Document ready.
// eslint-disable-next-line func-names
(function ($, drupalSettings, Drupal) {
  // React DOM render.
  function renderSearch() {
    ReactDOM.render(
      <SearchApp />,
      document.querySelector('#alshaya-algolia-autocomplete'),
    );
  }

  // We will trigger search only after activity is started.
  window.algoliaSearchActivityStarted = (typeof drupalSettings.algoliaSearch.showSearchResults === 'undefined')
    ? false
    : drupalSettings.algoliaSearch.showSearchResults;

  // For all pages, except Search.
  // Activate search only when user uses the search field.
  if (!window.algoliaSearchActivityStarted) {
    renderSearch();
  }

  // Focus event to activate search.
  $('#alshaya-algolia-autocomplete input[type="search"]').on('mousedown tap focus', () => {
    window.algoliaSearchActivityStarted = true;
  });

  // For search page - `/search`
  let searchInitiated = false;
  function initiateSearch() {
    if (searchInitiated) {
      return;
    }
    // Activate search if the tab has focus & search page.
    if (drupalSettings.algoliaSearch.showSearchResults === true && document.hasFocus()) {
      searchInitiated = true;
      renderSearch();
    }
  }

  // eslint-disable-next-line no-param-reassign
  Drupal.behaviors.searchAlgolia = {
    attach() {
      initiateSearch();
    },
  };
}(jQuery, drupalSettings, Drupal));
