import React from 'react';
import WishlistNotification from '../../../../alshaya_wishlist/js/components/wishlist-notification';
import { getWishlistLabel, getWishlistNotificationTime } from '../../../../alshaya_wishlist/js/utilities/wishlist-utils';
import ConditionalView from '../conditional-view';

export default class WishlistStickyHeader extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      wishListItemCount: 0,
      wishListItemData: null,
    };
  }

  componentDidMount() {
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
    if (productInfo) {
      // Check if sticky wrapper is active on screen.
      const querySelector = document.querySelector('.filter-fixed-top .sticky-filter-wrapper');
      if (querySelector === null) {
        return;
      }
      this.setTimer();
      this.setState({
        wishListItemData: productInfo,
      });
    }
  };

  render() {
    const { wishListItemCount, wishListItemData } = this.state;
    const wishlistActiveClass = wishListItemCount !== 0 ? 'wishlist-active' : 'wishlist-inactive';
    return (
      <div className="wishlist-header sticky-wrapper">
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
