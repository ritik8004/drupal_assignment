import React from 'react';
import {
  isProductExistInWishList,
  addProductToWishList,
  removeProductFromWishList,
  getWishlistLabel,
} from '../../utilities/wishlist-utils';

class WishlistButton extends React.Component {
  constructor(props) {
    super(props);

    // Set the products status in state.
    // true: if sku exist in wishlist,
    // false: default, if sku doesn't exist in wishlist.
    this.state = {
      addedInWishList: false,
      skuCode: props.skuCode ? props.skuCode : props.sku,
      variantSelected: props.variantSelected ? props.variantSelected : null,
      options: props.options ? props.options : null,
    };
  }

  componentDidMount = () => {
    const { skuCode } = this.state;
    const { context } = this.props;
    const { configurableCombinations } = drupalSettings;
    // @todo: we need to listen wishlist load event that
    // will trigger from header wishlist component after
    // wishlist data are fetched from MDC on page load
    // for logged in user.
    // Check if product already exist in wishlist, and
    // set the status for the sku.
    if (isProductExistInWishList(skuCode)) {
      this.updateWishListStatus(true);
    }

    if (context !== 'productDrawer' && configurableCombinations) {
      this.getSelectedOptions(configurableCombinations);
    }

    // Rendering wishlist button as per sku variant info.
    // Event listener is not required for new pdp.
    if (context !== 'magazinev2') {
      document.addEventListener('onSkuVariantSelect', this.updateProductInfoData, false);
    }
  };

  /**
   * This will update the selected options state of product.
   *
   * @param {bool} configurableCombinations
   *  Contains configurable options for grouped product.
   */
  getSelectedOptions = (configurableCombinations) => {
    const { sku } = this.props;
    const { variantSelected } = this.state;
    if (configurableCombinations[sku].bySku[variantSelected]) {
      this.setState({
        options: configurableCombinations[sku].bySku[variantSelected],
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
   * Fetch product title for main sku or variant selected.
   */
  getProductTitle = (currentSku, variantSku, variants, productInfo) => {
    const { sku } = this.props;
    if (productInfo[currentSku]) {
      return productInfo[currentSku].cart_title;
    } if (productInfo[sku] && variants !== null) {
      return variants[variantSku].cart_title;
    }
    return '';
  }

  /**
   * Add or remove product from the wishlist.
   */
  toggleWishlist = () => {
    const { addedInWishList, skuCode, options } = this.state;
    const { title, sku } = this.props;
    // If product already in wishlist remove this else add.
    if (addedInWishList) {
      removeProductFromWishList(skuCode, this.updateWishListStatus);
      return;
    }

    const productData = { sku, title, options };
    addProductToWishList(productData, this.updateWishListStatus);
  }

  /**
   * Update wishlist button state as per variant selection.
   */
  updateProductInfoData = (e) => {
    e.preventDefault();
    if (e.detail && e.detail.data.sku && e.detail.data.variantSelected) {
      this.setState({
        skuCode: e.detail.data.sku,
        variantSelected: e.detail.data.variantSelected,
      }, () => {
        this.updateWishListStatus(isProductExistInWishList(e.detail.data.sku));
      });
    }
  }

  render() {
    const { addedInWishList } = this.state;
    const { context, position, format } = this.props;

    // Display format can be 'link' or 'icon'.
    const formatClass = format || 'icon';
    const classPrefix = `wishlist-${formatClass} ${context} ${position}`;
    const wishListButtonClass = addedInWishList ? 'in-wishlist wishlist-button-wrapper' : 'wishlist-button-wrapper';

    // Wishlist text for my-wishlist page.
    let buttonText = addedInWishList ? 'Remove' : 'Add to @wishlist_label';

    // Wishlist text for PDP layouts.
    const viewModes = ['pdp', 'magazinev2', 'modal', 'matchback', 'productDrawer'];
    if (viewModes.includes(context)) {
      buttonText = addedInWishList ? 'Added to @wishlist_label' : 'Add to @wishlist_label';
    }

    // Wishlist text for Basket page.
    if (context === 'cart') {
      buttonText = addedInWishList ? 'Remove from @wishlist_label' : 'Move to @wishlist_label';
    }

    return (
      <div
        className={wishListButtonClass}
        onClick={() => this.toggleWishlist()}
      >
        <div className={classPrefix}>
          {Drupal.t(buttonText, { '@wishlist_label': getWishlistLabel() }, { context: 'wishlist' })}
        </div>
      </div>
    );
  }
}

export default WishlistButton;
