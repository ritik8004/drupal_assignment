import React from 'react';
import Popup from 'reactjs-popup';
import CheckoutItemImage from '../../../utilities/checkout-item-image';
import ConditionalView from '../../../../../js/utilities/components/conditional-view';
import {
  isAnonymousUser,
  addWishListInfoInStorage,
  addProductToWishList,
  getWishlistLabel,
  pushWishlistSeoGtmData,
} from '../../../../../js/utilities/wishlistHelper';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';
import dispatchCustomEvent from '../../../../../js/utilities/events';

export default class WishlistPopupBlock extends React.Component {
  constructor(props) {
    super(props);

    // Store reference to the main contiainer.
    this.buttonContainerRef = React.createRef();
  }

  addToWishlist = (addToWishlist) => {
    const {
      sku,
      title,
      options,
      closeWishlistModal,
      variant,
    } = this.props;
    // If user responds as yes, move item to wishlist and remove cart item.
    // Else close the popup and continue to remove cart item.
    const productInfo = {
      sku,
      variant,
      context: 'cart',
      title,
      options,
      element: this.buttonContainerRef.current,
    };

    if (addToWishlist) {
      // Add product to the wishlist. For guest users it'll store in local
      // storage and for logged in user this will store in backend using API
      // then will update the local storage as well.
      addProductToWishList(productInfo).then((response) => {
        if (typeof response.data.status !== 'undefined'
          && response.data.status) {
          // Push product values to GTM.
          pushWishlistSeoGtmData(productInfo);

          // Prepare and dispatch an event when product added to the storage
          // so other components like wishlist header can listen and do the
          // needful.
          dispatchCustomEvent('productAddedToWishlist', {
            productInfo,
            addedInWishList: true,
            removeFromCart: false,
          });

          // If user is logged in we need to update the products from
          // backend via api and update in local storage to get
          // the wishlist_item_id from the backend that we use while
          // removing the product from backend for logged in user.
          if (!isAnonymousUser()) {
            // Load wishlist information from the magento backend.
            window.commerceBackend.getWishlistFromBackend().then((responseData) => {
              if (hasValue(responseData.data.items)) {
                const wishListItems = {};

                responseData.data.items.forEach((item) => {
                  wishListItems[item.sku] = {
                    sku: item.sku,
                    options: item.options,
                    // We need this for removing the item from the wishlist.
                    wishlistItemId: item.wishlist_item_id,
                    // OOS status of product in backend.
                    inStock: item.is_in_stock,
                  };
                });

                // Save back to storage.
                addWishListInfoInStorage(wishListItems);

                // Prepare and dispatch an event when product added to the storage
                // so other components like wishlist header can listen and do the
                // needful.
                dispatchCustomEvent('productAddedToWishlist', {
                  productInfo,
                  extraOptions: {
                    notification: false,
                  },
                });
              }
            });
          }
        }
      });
    }
    // If user clicked on yes/no in popup, we pass true as response.
    closeWishlistModal(true);
  }

  closeModal = () => {
    const { closeWishlistModal } = this.props;
    // If user simply clicks on close, we pass false as response.
    closeWishlistModal(false);
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
            <a className="close-modal" onClick={() => this.closeModal()}>Close</a>
            <div className="wishlist-image-container">
              <ConditionalView condition={itemImage}>
                <CheckoutItemImage img_data={itemImage} />
              </ConditionalView>
              <div className="wishlist-question">
                {Drupal.t('Do you want to move this item to @wishlist_label?', { '@wishlist_label': getWishlistLabel() }, { context: 'wishlist' })}
              </div>
            </div>
            <div className="wishlist-options">
              <button
                className="wishlist-yes"
                id="wishlist-yes"
                type="button"
                onClick={() => this.addToWishlist(true)}
                ref={this.buttonContainerRef}
              >
                {Drupal.t('Yes, move to @wishlist_label', { '@wishlist_label': getWishlistLabel() }, { context: 'wishlist' })}
              </button>
              <button
                className="wishlist-no"
                id="wishlist-no"
                type="button"
                onClick={() => this.addToWishlist(false)}
              >
                {Drupal.t('No, remove it', {}, { context: 'wishlist' })}
              </button>
            </div>
          </div>
        </Popup>
      </div>
    );
  }
}
