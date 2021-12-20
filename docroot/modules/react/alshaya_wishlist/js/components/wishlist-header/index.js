import React from 'react';
import ConditionalView from '../../../../js/utilities/components/conditional-view';
import { smoothScrollTo } from '../../../../js/utilities/smoothScroll';
import { getWishlistLabel, getWishlistNotificationTime } from '../../utilities/wishlist-utils';
import WishlistNotification from '../wishlist-notification';

export default class WishlistHeader extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      wishListItemCount: 0,
      wishListItemData: null,
    };
  }

  componentDidMount() {
    // @todo Add logic to get wishlist content for current user.

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
