import React from 'react';
import ConditionalView from '../../../../js/utilities/components/conditional-view';
import { smoothScrollTo } from '../../../../js/utilities/smoothScroll';
import {
  isAnonymousUser,
  addWishListInfoInStorage,
  getWishListData,
  getWishlistLabel,
  getWishlistNotificationTime,
  loggedInUserStorageKey,
} from '../../../../js/utilities/wishlistHelper';
import WishlistNotification from '../wishlist-notification';
import { hasValue } from '../../../../js/utilities/conditionsUtility';
import { isDesktop } from '../../../../js/utilities/display';

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

    // Check if user is an authenticate user, add an event listener for wishlist
    // items load from MDC backend.
    if (!isAnonymousUser()) {
      document.addEventListener('getWishlistFromBackendSuccess', this.updateWishListHeader);
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
