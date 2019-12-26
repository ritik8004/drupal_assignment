import React from 'react';
import ReactDOM from 'react-dom';
import App from './App';

// Start instant search only after Document ready.
(function ($, Drupal) {
  ReactDOM.render(
    <App />,
    document.querySelector('#alshaya-algolia-autocomplete')
  );
})(jQuery);
