import React from 'react';
import {
  isProductExistInWishList,
  addProductToWishList,
  removeProductFromWishList,
} from '../../utilities/wishlist-utils';

class WishlistButton extends React.Component {
  constructor(props) {
    super(props);

    // Set the products status in state.
    // true: if sku exist in wishlist,
    // false: default, if sku doesn't exist in wishlist.
    this.state = {
      addedInWishList: false,
    };
  }

  componentDidMount = () => {
    const { sku } = this.props;

    // @todo: we need to listen wishlist load event that
    // will trigger from header wishlist component after
    // wishlist data are fetched from MDC on page load
    // for logged in user.
    // Check if product already exist in wishlist, and
    // set the status for the sku.
    if (isProductExistInWishList(sku)) {
      this.updateWishListStatus(true);
    }
  };

  /**
   * This will update the addedInWishList state of product.
   *
   * @param {bool} status
   *  Contains the status or product in wishlist.
   */
  updateWishListStatus = (status) => {
    const { addedInWishList } = this.state;
    if (addedInWishList !== status) {
      this.setState({
        addedInWishList: status,
      });
    }
  }

  /**
   * Add or remove product from the wishlist.
   */
  toggleWishlist = () => {
    const { addedInWishList } = this.state;
    const { sku } = this.props;

    // If product already in wishlist remove this else add.
    if (addedInWishList) {
      removeProductFromWishList(sku, this.updateWishListStatus);
    } else {
      addProductToWishList(sku, this.updateWishListStatus);
    }
  }

  render() {
    const { addedInWishList } = this.state;
    const { context, position } = this.props;

    const classPrefix = `wishlist-icon ${context} ${position}`;
    const wishListButtonClass = addedInWishList ? `${classPrefix} in-wishlist` : classPrefix;

    return (
      <div
        className={wishListButtonClass}
        onClick={() => this.toggleWishlist()}
      >
        {/* @todo: Display wishlist icon here. */}
        {Drupal.t('Add to wishlist')}
      </div>
    );
  }
}

export default WishlistButton;
