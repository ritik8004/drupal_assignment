import React from 'react';
import ReactDOM from 'react-dom';
import Cart from './cart/components/cart';

// Store the page type for conditions later.
window.spcPageType = 'cart';

ReactDOM.render(
  <Cart />,
  document.getElementById('spc-cart'),
);
