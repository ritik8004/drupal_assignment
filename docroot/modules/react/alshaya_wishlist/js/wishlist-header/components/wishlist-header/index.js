import React from 'react';
import ConditionalView from '../../../../../js/utilities/components/conditional-view';
import WishlistNotification from '../wishlist-notification';

export default class WishlistHeader extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      productCount: null,
      productData: {
        link: 'abc',
        name: 'abc abc',
      },
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
    const { productCount, productData } = this.state;
    const wishlistActiveClass = productCount !== null ? 'wishlist-active' : 'wishlist-blank';
    return (
      <div className="wishlist-header">
        <a className={`wishlist-link ${wishlistActiveClass}`} href={Drupal.url('my-wishlist')}>
          <span className="wishlist-icon">{Drupal.t('my @label', { '@label': drupalSettings.wishlist.label })}</span>
        </a>
        <ConditionalView condition={productData !== null}>
          <WishlistNotification
            productData={productData}
          />
        </ConditionalView>
      </div>
    );
  }
}
