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
      productInfo: props.productInfo,
    };
  }

  componentDidMount = () => {
    const { productInfo } = this.state;

    // @todo: we need to listen wishlist load event that
    // will trigger from header wishlist component after
    // wishlist data are fetched from MDC on page load
    // for logged in user.
    // Check if product already exist in wishlist, and
    // set the status for the sku.
    if (isProductExistInWishList(productInfo.sku)) {
      this.updateWishListStatus(true);
    }

    // Rendering wishlist button as per sku variant info.
    document.addEventListener('onSkuVariantSelect', this.updateProductInfoData, false);
  };

  /**
   * To product info state as per variant selection.
   */
  updateProductInfoData = (e) => {
    const { context } = this.props;
    if (e.detail && e.detail.data !== '' && context === 'pdp') {
      const variantInfo = e.detail.data;
      const productInfo = {
        sku: variantInfo.parent_sku ? variantInfo.parent_sku : variantInfo.sku,
        title: variantInfo.title,
      };
      this.setState({ productInfo }, () => {
        this.updateWishListStatus(isProductExistInWishList(productInfo.sku));
      });
    }
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
    const { addedInWishList, productInfo } = this.state;

    // If product already in wishlist remove this else add.
    if (addedInWishList) {
      removeProductFromWishList(productInfo.sku, this.updateWishListStatus);
    } else {
      addProductToWishList(productInfo, this.updateWishListStatus);
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
