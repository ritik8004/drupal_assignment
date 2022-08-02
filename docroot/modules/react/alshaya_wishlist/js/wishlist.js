import React from 'react';
import ReactDOM from 'react-dom';
import WishlistButton from './components/wishlist-button';

/**
 * Function to render Wishlist button
 * for the given element selector.
 *
 * @param {string} elementSelector
 *  Element selector for rendering wishlist button.
 * @param {string} context
 *  Context for PDP view mode.
 */
const renderWishListButton = (
  elementSelector,
  context,
  extraOptions = null,
) => {
  const selectedElements = document.getElementsByClassName(elementSelector);
  Array.from(selectedElements).forEach((element) => {
    // Get sku from closest article element.
    const sku = element.closest('article').getAttribute('data-sku');
    if (sku && sku !== null) {
      ReactDOM.render(
        <WishlistButton
          sku={sku}
          context={context}
          position="top-right"
          format="icon"
          extraOptions={extraOptions}
        />,
        element,
      );
    }
  });
};

/**
 * Method to handle the modal on load event and render component.
 */
const handleModalOnLoad = () => {
  renderWishListButton(
    'wishlist-pdp-modal',
    'modal',
    { notification: false },
  );
};

/**
 * Method to handle the matchback on load event and render component.
 */
const handleMatchBackLoad = () => {
  renderWishListButton('wishlist-pdp-matchback', 'matchback');
};

// Render wishlist button once sku base form is loaded.
document.addEventListener('onSkuBaseFormPreLoad', () => {
  // Check if the wishlist element on PDP exists and
  // data-sku is present, then render the wishlist button.
  renderWishListButton('wishlist-pdp-full', 'pdp');
});

// Add modal load event listener to render
// wishlist button whenever modal opens.
document.addEventListener('onModalLoad', handleModalOnLoad);

// Add modal load event listener to render
// wishlist button whenever modal opens.
document.addEventListener('onMatchbackLoad', handleMatchBackLoad);
