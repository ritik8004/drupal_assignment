import React from 'react';
import Popup from 'reactjs-popup';
import { addProductToWishList, getWishlistLabel } from '../../utilities/wishlist-utils';

export default class WishlistPopupBlock extends React.Component {
  addToWishlist = (addToWishlist) => {
    const {
      productInfo, closeWishlistModal,
    } = this.props;
    // If user responds as yes, move item to wishlist and remove cart item.
    // Else close the popup and continue to remove cart item.
    if (addToWishlist) {
      addProductToWishList(productInfo);
    }
    closeWishlistModal();
  }

  render() {
    return (
      <div className="wishlist-popup-container">
        <Popup
          open
          className="wishlist-confirmation"
          closeOnDocumentClick={false}
          closeOnEscape={false}
        >
          <div className="wishlist-popup-block">
            <div className="wishlist-question">
              {Drupal.t('Do you want to move the product to @wishlist_label?', { '@wishlist_label': getWishlistLabel() }, { context: 'wishlist' })}
            </div>
            <div className="wishlist-options">
              <button
                className="wishlist-yes"
                id="wishlist-yes"
                type="button"
                onClick={() => this.addToWishlist(true)}
              >
                {Drupal.t('Yes')}
              </button>
              <button
                className="wishlist-no"
                id="wishlist-no"
                type="button"
                onClick={() => this.addToWishlist(false)}
              >
                {Drupal.t('No')}
              </button>
            </div>
          </div>
        </Popup>
      </div>
    );
  }
}
