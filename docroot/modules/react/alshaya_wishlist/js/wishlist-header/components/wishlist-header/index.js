import React from 'react';
import ConditionalView from '../../../../../js/utilities/components/conditional-view';
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
    document.addEventListener('addWishlistNotification', this.addWishlistNotification);
  }

  addWishlistNotification = (e) => {
    if (e.detail) {
      this.setState({
        wishListItemData: e.detail,
      });
    }
  };

  render() {
    const { wishListItemCount, wishListItemData } = this.state;
    const wishlistActiveClass = wishListItemCount !== 0 ? 'wishlist-active' : 'wishlist-inactive';
    return (
      <div className="wishlist-header">
        <a className={`wishlist-link ${wishlistActiveClass}`} href={Drupal.url('my-wishlist')}>
          <span className="wishlist-icon">{Drupal.t('my @label', { '@label': drupalSettings.wishlist.label }, { context: 'wishlist' })}</span>
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
