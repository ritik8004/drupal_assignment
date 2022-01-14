import React from 'react';
import ConditionalView from '../../../../js/utilities/components/conditional-view';
import { smoothScrollTo } from '../../../../js/utilities/smoothScroll';
import {
  isAnonymousUser,
  addWishListInfoInStorage,
  getWishListData,
  getWishlistLabel,
  getWishlistNotificationTime,
  getWishlistFromBackend,
  addRemoveWishlistItemsInBackend,
  guestUserStorageKey,
  loggedInUserStorageKey,
} from '../../../../js/utilities/wishlistHelper';
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
      notificationItemData: null,
      headerClass: 'header-wrapper',
    };
  }

  componentDidMount() {
    // If user is an anonymous user, we need to remove any wishlist
    // info from logged in user in local storage.
    if (isAnonymousUser()) {
      // Remove wishlist info from local storage for logged in users.
      addWishListInfoInStorage({}, loggedInUserStorageKey());
    }

    // Check if user is an authenticate user, we have wishlist data
    // in local storage from guest users and merge wishlist info for logged
    // in user is true, we will call backend api to merge wishlist info
    // in magento for the customer. Once it is merged, get updated
    // data from magento and update the wishlist info data in local storage.
    // If merge flag is false, we will check for wishlist data in local storage
    // for logged in user and if found nothing, we will load from backend.
    if (!isAnonymousUser()) {
      // Guest user's wishlist data from local storage.
      const wishListDataOfGuestUser = getWishListData(guestUserStorageKey());

      // Remove wishlist info from local storage for guest users, as
      // we don't want this info to share with logged in users.
      addWishListInfoInStorage({}, guestUserStorageKey());

      if (wishListDataOfGuestUser
        && hasValue(drupalSettings.wishlist.mergeWishlistForLoggedInUsers)) {
        // Merge wishlist information to the magento backend from local storage,
        // if wishlist data available in local storage and merging wishlist
        // data flag is set to true.

        // Prepare the wishlist item data to push in backend api.
        const itemData = [];
        Object.values(wishListDataOfGuestUser).forEach((item) => {
          // Prepare sku options if available to push in backend api.
          const skuOptions = [];
          if (item.options.length > 0) {
            item.options.forEach((option) => {
              skuOptions.push({
                id: option.option_id,
                value: option.option_value,
              });
            });
          }

          itemData.push({
            sku: item.sku,
            options: skuOptions,
          });
        });

        // If we have items for wishlist then add the items in backend
        // wishlist with api call. Once added successfully, we need to
        // load the latest wishlist information from backend as well.
        if (itemData.length > 0) {
          addRemoveWishlistItemsInBackend(
            itemData,
            'mergeWishlistItems',
          ).then((response) => {
            if (typeof response.data.status !== 'undefined'
              && response.data.status) {
              this.loadWishlistFromBackend();
            }
          });
        }
      } else {
        // Get wishlist data from the local storage.
        const wishListData = getWishListData();
        if (wishListData === null
          || (typeof wishListData === 'object'
          && Object.keys(wishListData).length === 0)) {
          // Load wishlist information from the magento backend, if wishlist
          // data is empty in local storage for authenticate users. First check
          // if wishlist is available for the customer in backend.
          this.loadWishlistFromBackend();
        }
      }
    }

    // Add event listener for add to wishlist action.
    document.addEventListener('productAddedToWishlist', this.handleAddToWishList, false);

    // Add event listener for remove product to wishlist action.
    document.addEventListener('productRemovedFromWishlist', this.handleRemoveToWishList, false);
  }

  componentWillUnmount() {
    // Clear notification timeout.
    clearTimeout(this.timer);
  }

  /**
   * Helper function to load wishlist information from the magento backend.
   */
  loadWishlistFromBackend = () => {
    getWishlistFromBackend().then((response) => {
      if (hasValue(response.data.items)) {
        const wishListItems = {};

        response.data.items.forEach((item) => {
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
  };

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
        notificationItemData: null,
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
    if (productInfo) {
      // Get the wishlist items from the local storage
      // and set the count in state.
      const wishListItems = getWishListData() || {};
      const wishListItemCount = Object.keys(wishListItems).length;

      // Prepare an object to update the component state.
      const stateData = { wishListItemCount };

      // Check for the extra configurable options are available.
      const { extraOptions } = data.detail;

      // Check if extra configurations are empty or if notification flag
      // is available should not be false to show the notification.
      if (!hasValue(extraOptions)
        || (hasValue(extraOptions.notification)
        && extraOptions.notification)) {
        // Set timer for the wishlist notification.
        this.setTimer();

        // Set the notification data.
        stateData.notificationItemData = productInfo || null;

        // Check if sticky wrapper is active on screen.
        const querySelector = document.querySelector('.filter-fixed-top .sticky-filter-wrapper');
        // If sticky header is not present, scroll user to header.
        // Else show notification on sticky header.
        if (querySelector === null) {
          smoothScrollTo('#wishlist-header-wrapper');
        } else {
          stateData.headerClass = 'sticky-wrapper';
        }
      }

      // Update component state data.
      this.setState(stateData);
    }
  };

  /**
   * Once item is removed from wishlist,
   * check and update the header icon state.
   */
  handleRemoveToWishList = () => {
    // Get the wishlist items from the local storage
    // and set the count in state.
    const wishListItems = getWishListData() || {};
    const wishListItemCount = Object.keys(wishListItems).length;
    this.setState({ wishListItemCount });
  };

  render() {
    const { wishListItemCount, notificationItemData, headerClass } = this.state;
    const wishlistActiveClass = wishListItemCount !== 0 ? 'wishlist-active' : 'wishlist-inactive';
    return (
      <div className={`wishlist-header ${headerClass}`}>
        <a className={`wishlist-link ${wishlistActiveClass}`} href={Drupal.url('wishlist')}>
          <span className="wishlist-icon">{Drupal.t('my @wishlist_label', { '@wishlist_label': getWishlistLabel() }, { context: 'wishlist' })}</span>
        </a>
        <ConditionalView condition={notificationItemData !== null}>
          <WishlistNotification
            notificationItemData={notificationItemData}
          />
        </ConditionalView>
      </div>
    );
  }
}
