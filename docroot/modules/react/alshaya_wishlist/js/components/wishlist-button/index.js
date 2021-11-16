import React from 'react';
import {
  isProductExistInWishList,
  addProductToWishList,
  removeProductFromWishList,
} from '../../utilities/wishlist-utils';

class WishlistButton extends React.Component {
  constructor(props) {
    super(props);
    let addedInWishList = false;
    const { sku } = props;

    // Check if product already exist in wishlist.
    if (isProductExistInWishList(sku)) {
      addedInWishList = true;
    }

    // Set the products status in state.
    // true: if sku exist in wishlist,
    // false: if sku doesn't exist in wishlist.
    this.state = {
      addedInWishList,
    };
  }

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
    const wishListButtonClass = addedInWishList ? `${classPrefix}  added` : classPrefix;

    return (
      <div
        className={wishListButtonClass}
        onClick={() => this.toggleWishlist()}
      >
        {Drupal.t('Add to wishlist')}
      </div>
    );
  }
}

export default WishlistButton;
