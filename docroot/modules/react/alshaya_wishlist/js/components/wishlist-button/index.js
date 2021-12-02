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
      addProductToWishList({ sku }, this.updateWishListStatus);
    }
  }

  render() {
    const { addedInWishList } = this.state;
    const { context, position, format } = this.props;

    // Display format can be 'link' or 'icon'.
    const formatClass = format || 'icon';
    const classPrefix = `wishlist-${formatClass} ${context} ${position}`;
    const wishListButtonClass = addedInWishList ? `${classPrefix} in-wishlist` : classPrefix;
    const buttonText = addedInWishList ? 'Remove' : 'Add to wishlist';

    return (
      <div
        className={wishListButtonClass}
        onClick={() => this.toggleWishlist()}
      >
        {/* @todo: Display wishlist icon here. */}
        {Drupal.t(buttonText)}
      </div>
    );
  }
}

export default WishlistButton;
