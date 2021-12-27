import React from 'react';
import ShareIcon from './share-icon';
import SharePopup from './share-popup';
import ConditionalView from '../../../../js/utilities/components/conditional-view';
import { getWishlistShareLink } from '../../utilities/wishlist-utils';
import { getWishListData, isAnonymousUser } from '../../../../js/utilities/wishlistHelper';

class WishlistShare extends React.Component {
  constructor(props) {
    super(props);

    // Get the wishlist items.
    const wishListItems = getWishListData() || {};
    const wishListItemsCount = Object.keys(wishListItems).length;

    this.state = {
      showSharePopup: false,
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
   * To open the wishlist share popup.
   * Popup will show up while clicking on share link.
   */
  openWishListShareModal = () => {
    this.setState({
      showSharePopup: true,
    });
  }

  /**
   * To close the wishlist share popup.
   */
  closeWishlistShareModal = () => {
    this.setState({
      showSharePopup: false,
    });
  };

  render() {
    const { wishListItemsCount, showSharePopup } = this.state;

    // Return if there are no items in wishlist info.
    if (!wishListItemsCount) {
      return null;
    }

    return (
      <>
        <button type="button" onClick={this.openWishListShareModal}>
          <span className="text">{Drupal.t('Share All', {}, { context: 'wishlist' })}</span>
          <span className="icon"><ShareIcon /></span>
        </button>
        <ConditionalView condition={showSharePopup}>
          <SharePopup
            wishlistShareLink={getWishlistShareLink()}
            closeWishlistShareModal={this.closeWishlistShareModal}
          />
        </ConditionalView>
      </>
    );
  }
}

export default WishlistShare;
