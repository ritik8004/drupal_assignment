import React from 'react';
import Popup from 'reactjs-popup';
import CheckoutItemImage from '../../../../alshaya_spc/js/utilities/checkout-item-image';
import ConditionalView from '../../../../js/utilities/components/conditional-view';
import { addProductToWishList, getWishlistLabel } from '../../utilities/wishlist-utils';

export default class WishlistPopupBlock extends React.Component {
  addToWishlist = (addToWishlist) => {
    const {
      sku, title, closeWishlistModal,
    } = this.props;
    // If user responds as yes, move item to wishlist and remove cart item.
    // Else close the popup and continue to remove cart item.
    const productInfo = { sku, title };
    if (addToWishlist) {
      addProductToWishList(productInfo);
    }
    closeWishlistModal();
  }

  closeModal = () => {
    const { closeWishlistModal } = this.props;
    closeWishlistModal('close modal');
  }

  render() {
    const { itemImage } = this.props;
    return (
      <div className="wishlist-popup-container">
        <Popup
          open
          className="wishlist-confirmation"
          closeOnDocumentClick={false}
          closeOnEscape={false}
        >
          <div className="wishlist-popup-block">
            <ConditionalView condition={itemImage}>
              <CheckoutItemImage img_data={itemImage} />
            </ConditionalView>
            <a className="close-modal" onClick={() => this.closeModal()}>Close</a>
            <div className="wishlist-question">
              {Drupal.t('Do you want to move this item to @wishlist_label?', { '@wishlist_label': getWishlistLabel() }, { context: 'wishlist' })}
            </div>
            <div className="wishlist-options">
              <button
                className="wishlist-yes"
                id="wishlist-yes"
                type="button"
                onClick={() => this.addToWishlist(true)}
              >
                {Drupal.t('Yes, move to @wishlist_label', { '@wishlist_label': getWishlistLabel() }, { context: 'wishlist' })}
              </button>
              <button
                className="wishlist-no"
                id="wishlist-no"
                type="button"
                onClick={() => this.addToWishlist(false)}
              >
                {Drupal.t('No, remove it')}
              </button>
            </div>
          </div>
        </Popup>
      </div>
    );
  }
}
