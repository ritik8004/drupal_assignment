import React from 'react';
import ReactDOM from 'react-dom';
import WishlistButton from './components/wishlist-button';

/**
 * Function to render Wishlist button
 * for the given element selector.
 *
 * @param {string} elementSelector
 *  Element selector for rendering wishlist button.
 */
const renderWishListButton = (elementSelector) => {
  const selectedEelement = document.querySelector(elementSelector);
  if (selectedEelement) {
    const { sku } = selectedEelement.closest('article').dataset;
    if (sku) {
      ReactDOM.render(
        <WishlistButton
          sku={sku}
          context="pdp"
          position="top-right"
        />,
        selectedEelement,
      );
    }
  }
};

/**
 * Method to handle the modal on load event and render component.
 */
const handleModalOnLoad = () => {
  renderWishListButton('.wishlist-pdp-modal');
};

// Check if the wishlist element on PDP exist and
// data-sku is present, then render the wishlist button.
renderWishListButton('.wishlist-pdp-full');

// Add modal load event listener to render
// wishlist button whenever modal opens.
document.addEventListener('onModalLoad', handleModalOnLoad);
