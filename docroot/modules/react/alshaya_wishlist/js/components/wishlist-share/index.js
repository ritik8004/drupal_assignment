import React from 'react';
import SharePopup from './share-popup';
import ConditionalView from '../../../../js/utilities/components/conditional-view';
import {
  getWishListData,
  isAnonymousUser,
} from '../../../../js/utilities/wishlistHelper';
import { hasValue } from '../../../../js/utilities/conditionsUtility';
import getStringMessage from '../../../../js/utilities/strings';

class WishlistShare extends React.Component {
  constructor(props) {
    super(props);

    // Get the wishlist items.
    const wishListItems = getWishListData() || {};
    const wishListItemsCount = Object.keys(wishListItems).length;

    this.state = {
      wishlistShareLink: null,
      wishListItemsCount,
    };
  }

  /**
   * We need to listen events from load wishlist data from backend and remove
   * products from wishlist so we can update the share button status properly.
   */
  componentDidMount() {
    if (!isAnonymousUser()) {
      // Add event listener for get wishlist load event for logged in user.
      // This will execute when wishlist loaded from the backend
      // and page loads before.
      document.addEventListener('getWishlistFromBackendSuccess', this.toggleShareLink, false);
    }
    // Update share link after any product is removed.
    document.addEventListener('productRemovedFromWishlist', this.toggleShareLink, false);
  }

  /**
   * Remove event listners after component gets unmount.
   */
  componentWillUnmount() {
    if (!isAnonymousUser()) {
      document.removeEventListener('getWishlistFromBackendSuccess', this.updateWisListProductsList, false);
    }
    document.removeEventListener('productRemovedFromWishlist', this.updateWisListProductsList, false);
  }

  /**
   * Show/Hide the wishlist share link on products data availability.
   */
  toggleShareLink = () => {
    // Get the wishlist items.
    const wishListItems = getWishListData() || {};
    const wishListItemsCount = Object.keys(wishListItems).length;
    this.setState({ wishListItemsCount });
  };

  /**
   * On click handler for the share button link. If user is anonymous we
   * redirect user to login page else the modal popup will open.
   */
  onShareAllClick = () => {
    // Push share all button click to gtm.
    Drupal.alshayaSeoGtmPushShareWishlist('share all');
    // Redirect to login page if custom is not logged in.
    if (isAnonymousUser()) {
      window.location = Drupal.url(`user/login?destination=/${drupalSettings.path.pathPrefix}${drupalSettings.path.currentPath}`);
      return;
    }

    // Open wishlist share modal if custom is logged in.
    this.openWishListShareModal();
  };

  /**
   * Prepare the wishlist share link and open the wishlist share popup.
   * Popup will show up once we have share link created.
   */
  openWishListShareModal = () => {
    // Call magento api to get the wishlist details of current logged in user.
    window.commerceBackend.getWishlistInfoFromBackend().then((response) => {
      if (hasValue(response.data)) {
        if (hasValue(response.data.status)
          && hasValue(response.data.sharing_code)) {
          // Prepare the share wishlist url with wishlist
          // sharing code and user name.
          const encodedShareUrl = btoa(JSON.stringify({
            sharedCode: response.data.sharing_code,
            sharedUserName: drupalSettings.userDetails.userName || null,
          }));

          // Prepare the absolute link of wishlist share page for the
          // current logged in customer.
          const wishlistShareLink = Drupal.url.toAbsolute(Drupal.url(`wishlist/share?data=${encodedShareUrl}`));

          // Update the wishlist share link in state to open the popup.
          this.setState({ wishlistShareLink });
        }
      }
    });
  }

  /**
   * To close the wishlist share popup.
   */
  closeWishlistShareModal = () => {
    this.setState({
      wishlistShareLink: null,
    });
  };

  render() {
    const {
      wishListItemsCount,
      wishlistShareLink,
    } = this.state;

    // Return if there are no items in wishlist info.
    if (!wishListItemsCount) {
      return null;
    }

    return (
      <>
        <button type="button" onClick={this.onShareAllClick}>
          <span className="text">{getStringMessage('wishlist_share_all')}</span>
          <span className="icon" />
        </button>
        <ConditionalView condition={wishlistShareLink !== null}>
          <SharePopup
            wishlistShareLink={wishlistShareLink}
            closeWishlistShareModal={this.closeWishlistShareModal}
          />
        </ConditionalView>
      </>
    );
  }
}

export default WishlistShare;
