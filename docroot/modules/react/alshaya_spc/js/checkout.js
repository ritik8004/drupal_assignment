import React from 'react';
import ReactDOM from 'react-dom';
import Checkout from './checkout/components/checkout';

// Store the page type for conditions later.
window.spcPageType = 'checkout';

ReactDOM.render(
  <Checkout />,
  document.getElementById('spc-checkout'),
);
