import React from 'react';
import ReactDOM from 'react-dom';
import WishlistProductList from './components/wishlist-product-list';
import { isShareWishlistEnabled } from '../../js/utilities/wishlistHelper';
import WishlistShare from './components/wishlist-share';

ReactDOM.render(
  <WishlistProductList />,
  document.getElementById('my-wishlist'),
);


// Check if the wishlist sharing enabled render wishlist share widget.
if (isShareWishlistEnabled()) {
  // Get the page title block element.
  const titleBlockElement = document.getElementById('block-page-title');
  // If title block exist we create a new element for wishlist share.
  if (titleBlockElement) {
    const shareElement = document.createElement('div');
    shareElement.id = 'wishlist-share';
    titleBlockElement.appendChild(shareElement);
    // Render the wishlist share component with new element.
    ReactDOM.render(
      <WishlistShare />,
      document.getElementById('wishlist-share'),
    );
  }
}
