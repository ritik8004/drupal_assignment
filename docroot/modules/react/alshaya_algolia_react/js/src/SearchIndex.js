import React from 'react';
import ReactDOM from 'react-dom';
import SearchApp from './search/SearchApp';

// Start instant search only after Document ready.
// eslint-disable-next-line func-names
(function ($, drupalSettings) {
  // We will trigger search only after activity is started.
  window.algoliaSearchActivityStarted = (typeof drupalSettings.algoliaSearch.showSearchResults === 'undefined')
    ? false
    : drupalSettings.algoliaSearch.showSearchResults;

  ReactDOM.render(
    <SearchApp />,
    document.querySelector('#alshaya-algolia-autocomplete'),
  );

  $('#alshaya-algolia-autocomplete input[type="search"]').on('mousedown tap focus', () => {
    window.algoliaSearchActivityStarted = true;
  });
}(jQuery, drupalSettings));
