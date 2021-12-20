import React from 'react';
import ConditionalView from '../../../../js/utilities/components/conditional-view';
import { smoothScrollTo } from '../../../../js/utilities/smoothScroll';
import {
  getWishlistLabel,
  getWishlistNotificationTime,
  getWishListData,
  isAnonymousUser,
  getWishlistFromBackend,
  addWishListInfoInStorage,
} from '../../utilities/wishlist-utils';
import WishlistNotification from '../wishlist-notification';
import { hasValue } from '../../../../js/utilities/conditionsUtility';

export default class WishlistHeader extends React.Component {
  constructor(props) {
    super(props);

    // Get the wishlist items from the local storage
    // and set the count in state.
    const wishListItems = getWishListData() || {};
    const wishListItemCount = Object.keys(wishListItems).length;

    this.state = {
      wishListItemCount,
      wishListItemData: null,
    };
  }

  componentDidMount() {
    // Check if wishlist data is null and user is an authenticate user,
    // we will call backend api to get data from magento and
    // store the wishlist info data in local storage.
    if ((getWishListData() === null) && !isAnonymousUser()) {
      // Load wishlist information from the magento backend.
      getWishlistFromBackend().then((response) => {
        if (hasValue(response.data.items)) {
          const wishListItems = {};

          response.data.items.forEach((item) => {
            wishListItems[item.sku] = {
              sku: item.sku,
              options: item.options,
              // We need this for removing the item from the wishlist.
              wishlistItemId: item.wishlist_item_id,
            };
          });

          // Save back to storage.
          addWishListInfoInStorage(wishListItems);

          // Update the wishlist header icon color state
          // if we have product available in wishlist.
          const wishListItemCount = Object.keys(wishListItems).length;
          if (wishListItemCount > 0) {
            this.setState({ wishListItemCount });
          }

          // Dispatch an event for other modules to know
          // that wishlist data is available in storage.
          const getWishlistFromBackendSuccess = new CustomEvent('getWishlistFromBackendSuccess', { bubbles: true });
          document.dispatchEvent(getWishlistFromBackendSuccess);
        }
      });
    }

    // Add event listener for add to wishlist action.
    document.addEventListener('productAddedToWishlist', this.handleAddToWishList, false);
  }

  componentWillUnmount() {
    clearTimeout(this.timer);
  }

  /**
   * Set timer for wishlist notifcation.
   */
  setTimer() {
    if (this.timer != null) {
      clearTimeout(this.timer);
    }

    // Hide notification after certain milliseconds.
    this.timer = setTimeout(() => {
      this.setState({
        wishListItemData: null,
      });
      this.timer = null;
    }, getWishlistNotificationTime());
  }

  /**
   * Once item is added to wishlist, product details
   * are shown in notification panel.
   */
  handleAddToWishList = (data) => {
    const { productInfo } = data.detail;
    // Check if sticky wrapper is active on screen.
    const querySelector = document.querySelector('.filter-fixed-top .sticky-filter-wrapper');
    if (querySelector !== null) {
      return;
    }
    this.setTimer();
    this.setState({
      wishListItemData: productInfo,
    });
    smoothScrollTo('#wishlist-header-wrapper');
  };

  render() {
    const { wishListItemCount, wishListItemData } = this.state;
    const wishlistActiveClass = wishListItemCount !== 0 ? 'wishlist-active' : 'wishlist-inactive';
    return (
      <div className="wishlist-header top-wrapper">
        <a className={`wishlist-link ${wishlistActiveClass}`} href={Drupal.url('wishlist')}>
          <span className="wishlist-icon">{Drupal.t('my @wishlist_label', { '@wishlist_label': getWishlistLabel() }, { context: 'wishlist' })}</span>
        </a>
        <ConditionalView condition={wishListItemData !== null}>
          <WishlistNotification
            wishListItemData={wishListItemData}
          />
        </ConditionalView>
      </div>
    );
  }
}
