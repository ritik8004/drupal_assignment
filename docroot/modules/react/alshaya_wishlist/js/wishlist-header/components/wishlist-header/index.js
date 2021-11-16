import React from 'react';
import ConditionalView from '../../../../../js/utilities/components/conditional-view';
import WishlistNotification from '../wishlist-notification';

export default class WishlistHeader extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      productsQty: null,
      productData: null,
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
        productData: e.detail,
      });
    }
  };

  render() {
    const { productsQty, productData } = this.state;
    const wishlistActiveClass = productsQty !== null ? 'wishlist-active' : 'wishlist-blank';
    return (
      <div className="wishlist-header">
        <a className={`wishlist-link ${wishlistActiveClass}`} href={Drupal.url('my-wishlist')}>
          <span className="wishlist-icon">{Drupal.t('wishlist icon')}</span>
        </a>
        <ConditionalView condition={productData !== null}>
          <WishlistNotification
            productsData={productData}
          />
        </ConditionalView>
      </div>
    );
  }
}
