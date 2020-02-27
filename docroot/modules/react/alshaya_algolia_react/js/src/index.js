import React from 'react';
import ReactDOM from 'react-dom';
import App from './App';

// Start instant search only after Document ready.
(function ($, drupalSettings) {
  // We will trigger search only after activity is started.
  window.algoliaSearchActivityStarted = (typeof drupalSettings.algoliaSearch.showSearchResults === 'undefined')
    ? false
    : drupalSettings.algoliaSearch.showSearchResults;

  ReactDOM.render(
    <App />,
    document.querySelector('#alshaya-algolia-autocomplete')
  );

  $('#alshaya-algolia-autocomplete input[type="search"]').on('mousedown tap focus', function (event) {
    window.algoliaSearchActivityStarted = true;
  });
})(jQuery, drupalSettings);
