import React from 'react';
import ConditionalView from '../../../../js/utilities/components/conditional-view';
import { smoothScrollTo } from '../../../../js/utilities/smoothScroll';
import {
  getWishlistLabel,
  getWishlistNotificationTime,
  getWishListData,
  isAnonymousUser,
  getWishlistFromBackend,
} from '../../utilities/wishlist-utils';
import WishlistNotification from '../wishlist-notification';

export default class WishlistHeader extends React.Component {
  constructor(props) {
    super(props);

    // Get the wishlist items and set the count in state.
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
      getWishlistFromBackend();

      // Add event listener for get wishlist from backend success.
      document.addEventListener('getWishlistFromBackendSuccess', this.handleWishListItemsCount, false);
    }

    // Add event listener for add to wishlist action.
    document.addEventListener('productAddedToWishlist', this.handleAddToWishList, false);
  }

  componentWillUnmount() {
    clearTimeout(this.timer);
  }

  /**
   * Once item is available change the icon state in header.
   */
  handleWishListItemsCount = () => {
    // Get the wishlist items.
    const wishListItems = getWishListData() || {};
    const wishListItemCount = Object.keys(wishListItems).length;

    if (wishListItemCount > 0) {
      this.setState({ wishListItemCount });
    }
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
      <div className="wishlist-header">
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
