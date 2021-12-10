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
const renderWishListButton = (elementSelector, context, skuCode) => {
  let selectedEelement = null;
  let sku;
  if (skuCode && context === 'matchback') {
    selectedEelement = document.querySelector(`[data-matchback-sku="${skuCode}"]`);
    sku = skuCode;
  } else {
    selectedEelement = document.querySelector(elementSelector);
    if (selectedEelement !== null) {
      sku = selectedEelement.closest('article').getAttribute('data-sku');
    }
  }
  if (sku && selectedEelement !== null) {
    ReactDOM.render(
      <WishlistButton
        sku={sku}
        context={context}
        position="top-right"
        format="icon"
      />,
      selectedEelement,
    );
  }
};

/**
 * Method to handle the modal on load event and render component.
 */
const handleModalOnLoad = () => {
  renderWishListButton('.wishlist-pdp-modal', 'modal');
};

/**
 * Method to handle the matchback on load event and render component.
 */
const handleMatchBackLoad = (e) => {
  if (e.detail && e.detail.data) {
    const sku = e.detail.data;
    renderWishListButton('.wishlist-pdp-matchback', 'matchback', sku);
  }
};

// Check if the wishlist element on PDP exist and
// data-sku is present, then render the wishlist button.
renderWishListButton('.wishlist-pdp-full', 'pdp');

// Add modal load event listener to render
// wishlist button whenever modal opens.
document.addEventListener('onModalLoad', handleModalOnLoad);

// Add modal load event listener to render
// wishlist button whenever modal opens.
document.addEventListener('onMatchbackLoad', handleMatchBackLoad);