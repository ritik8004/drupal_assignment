import React from 'react';
import ReactDOM from 'react-dom';
import WishListPDP from './components/wishlist-pdp';

if (document.querySelector('#wishlist-pdp')) {
  ReactDOM.render(
    <WishListPDP />,
    document.querySelector('#wishlist-pdp'),
  );
}
