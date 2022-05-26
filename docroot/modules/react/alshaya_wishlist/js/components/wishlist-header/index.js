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
import { isDesktop } from '../../../../js/utilities/display';

/**
 * Flag used to check if backend api for wishlist items is already called.
 */
window.loadWishListFromBackend = window.loadWishListFromBackend || false;

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
      if (wishListDataOfGuestUser
        && typeof wishListDataOfGuestUser === 'object'
        && Object.keys(wishListDataOfGuestUser).length > 0
        && hasValue(drupalSettings.wishlist.mergeWishlistForLoggedInUsers)) {
        // Remove wishlist info from local storage for guest users, as
        // we don't want this info to share with logged in users.
        addWishListInfoInStorage({}, guestUserStorageKey());

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
          // Wishlist header component is placed in two different blocks header and
          // sticky header. Header component calls merge items and
          // loadWishlistFromBackend. To stop sticky header again calling
          // loadWishlistFromBackend we set the flag here. This is unset after merge
          // items call is ended.
          window.loadWishListFromBackend = true;
          addRemoveWishlistItemsInBackend(
            itemData,
            'mergeWishlistItems',
          ).then((response) => {
            if (typeof response.data.status !== 'undefined'
              && response.data.status) {
              // Wishlist header component is called in two different places,
              // header and sticky header. The header component calls
              // add remove wish list items to merge guest wishlist products
              // and loadWishlistFromBackend. Before merging items,
              // sticky header calls loadWishlistFromBackend in below else
              // condition. Hence the flag is set to stop sticky header from
              // loading wishlist items before merge. Here we override flag as
              // items should be refreshed after merged from guest list.
              window.loadWishListFromBackend = false;
              this.loadWishlistFromBackend();
            }
          });
        }
      } else {
        // Get wishlist data from the local storage.
        const wishListData = getWishListData();
        if (hasValue(drupalSettings.wishlist.config.forceLoadWishlistFromBackend)
          || (wishListData === null
          || (typeof wishListData === 'object'
          && Object.keys(wishListData).length === 0))) {
          // First clean the existing data in storage.
          addWishListInfoInStorage({});

          // Load wishlist information from the magento backend, if wishlist
          // data is empty in local storage for authenticate users. First check
          // if wishlist is available for the customer in backend.
          this.loadWishlistFromBackend();
          // Get wishlist count after backend success then update header.
          document.addEventListener('getWishlistFromBackendSuccess', this.updateWishListHeader);
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
   * Get wishlist count and update wishlist header.
   */
  updateWishListHeader = (e) => {
    const { wishListItemCount } = e.detail;
    // Update the wishlist header icon color state
    // if we have product available in wishlist.
    if (wishListItemCount > 0) {
      this.setState({ wishListItemCount });
    }
  }

  /**
   * Helper function to load wishlist information from the magento backend.
   */
  loadWishlistFromBackend = () => {
    // Since wishlist-header component is called more than once
    // as it is also used for sticky header, we only call API once
    // and use getWishlistFromBackendSuccess event to update header component.
    if (window.loadWishListFromBackend) {
      return;
    }
    window.loadWishListFromBackend = true;
    getWishlistFromBackend().then((response) => {
      let wishListItemCount = 0;
      if (hasValue(response.data.items)) {
        const wishListItems = [];

        response.data.items.forEach((item) => {
          wishListItems.push({
            sku: item.sku,
            options: item.options,
            // We need this for removing the item from the wishlist.
            wishlistItemId: item.wishlist_item_id,
            // OOS status of product in backend.
            inStock: item.is_in_stock,
          });
        });

        // Save back to storage.
        addWishListInfoInStorage(wishListItems);

        // Get wishlist item count.
        wishListItemCount = Object.keys(wishListItems).length;
      }
      // Dispatch an event for other modules to know
      // that wishlist data is available in storage.
      const getWishlistFromBackendSuccess = new CustomEvent('getWishlistFromBackendSuccess', {
        bubbles: true,
        detail: {
          wishListItemCount,
        },
      });
      document.dispatchEvent(getWishlistFromBackendSuccess);
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

        // Check if sticky header wrapper is active on screen.
        const stickyHeader = document.querySelector('body.header-sticky-filter');
        // Check if sticky search wrapper is active on screen.
        const stickySearch = document.querySelector('body.Sticky-algolia-search.header-sticky-filter');
        // If sticky header is not present, scroll user to header.
        // Else show notification on sticky header.
        if (stickyHeader === null || stickySearch !== null) {
          // By default scroll the user to body section.
          let scrollToSelector = 'body';
          if (!isDesktop()) {
            // If mobile or tablet then scroll the user to header section.
            scrollToSelector = '.c-header';
          }
          smoothScrollTo(scrollToSelector);
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
