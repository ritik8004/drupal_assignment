import React from 'react';
import ReactDOM from 'react-dom';
import WishlistButton from './components/wishlist-button';
import { getProductSkuOnPDP } from './utilities/wishlist-utils';

// @todo: need to check this for PDP modal view as well.
if (document.querySelector('#wishlist-pdp')) {
  const sku = getProductSkuOnPDP();
  if (sku) {
    ReactDOM.render(
      <WishlistButton
        context="pdp"
        position="top-right"
        sku={sku}
      />,
      document.querySelector('#wishlist-pdp'),
    );
  }
}
