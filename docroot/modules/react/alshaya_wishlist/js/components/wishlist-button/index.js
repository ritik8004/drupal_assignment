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
    // Setting variant selected for current variant.
    // Options are selected attribute options for default product.
    this.state = {
      addedInWishList: false,
      skuCode: props.skuCode ? props.skuCode : props.sku,
      variantSelected: props.variantSelected ? props.variantSelected : null,
      options: props.options ? props.options : null,
      title: props.title ? props.title : '',
    };
  }

  componentDidMount = () => {
    const { skuCode, variantSelected } = this.state;
    const { context, sku } = this.props;
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

    // We pass options directly for plp product drawer
    // So we only need to get options for pdp layouts
    // Also, check if it is configurable product.
    if (context !== 'productDrawer' && context !== 'cart'
      && this.isConfigurableProduct(sku, configurableCombinations)
      && variantSelected !== null) {
      this.getSelectedOptions(variantSelected, configurableCombinations[sku]);
    }
    // Set title for simple sku product on page load.
    // We need to set title only for old pdp, modal and matchback.
    // For new pdp and side drawer, we get all data through props.
    const viewModes = ['pdp', 'modal', 'matchback'];
    if (!(this.isConfigurableProduct(sku, configurableCombinations))
      && viewModes.includes(context)) {
      const productKey = context === 'matchback' ? 'matchback' : 'productInfo';
      const productInfo = drupalSettings[productKey];
      this.setState({
        title: productInfo[sku].cart_title,
      });
    }

    // Rendering wishlist button as per sku variant info.
    // Event listener is not required for new pdp.
    if (context !== 'magazinev2') {
      document.addEventListener('onSkuVariantSelect', this.updateProductInfoData, false);
    }
  };

  /**
   * Check if current product if configurable.
   *
   * @param {string} sku
   *  Sku code of product.
   *
   * @param {object} configurableCombinations
   *  Contains configurable options for grouped product.
   */
  isConfigurableProduct = (sku, configurableCombinations) => {
    if (configurableCombinations && configurableCombinations[sku]) {
      return true;
    }
    return false;
  }

  /**
   * This will update the selected options state of product.
   *
   * @param {object} configurableCombinations
   *  Contains configurable options for grouped product.
   */
  getSelectedOptions = (variantSelected, configurableCombinations) => {
    if (configurableCombinations.bySku[variantSelected]) {
      const options = [];
      Object.keys(configurableCombinations.bySku[variantSelected]).forEach((key) => {
        const option = {
          option_id: configurableCombinations.configurables[key].attribute_id,
          option_value: configurableCombinations.bySku[variantSelected][key],
        };

        // Skipping the psudo attributes.
        if (drupalSettings.psudo_attribute === undefined
          || drupalSettings.psudo_attribute !== option.option_id) {
          options.push(option);
        }
      });
      this.setState({ options });
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
    const {
      addedInWishList, skuCode, options, title,
    } = this.state;

    // If product already in wishlist remove this else add.
    if (addedInWishList) {
      removeProductFromWishList(skuCode, this.updateWishListStatus);
      return;
    }

    const productData = {
      sku: skuCode,
      title,
      options,
    };
    addProductToWishList(productData, this.updateWishListStatus);
  }

  /**
   * Update wishlist button state as per variant selection.
   */
  updateProductInfoData = (e) => {
    e.preventDefault();
    if (e.detail && e.detail.data.sku && e.detail.data.variantSelected) {
      const { sku } = this.props;
      const { configurableCombinations } = drupalSettings;
      this.setState({
        skuCode: e.detail.data.sku,
        variantSelected: e.detail.data.variantSelected,
        title: e.detail.data.title,
      }, () => {
        // Update wishlist button status for selected variant.
        this.updateWishListStatus(isProductExistInWishList(e.detail.data.sku));
        // Get selected attribute options for selected variant.
        if (this.isConfigurableProduct(sku, configurableCombinations)
          && e.detail.data.variantSelected) {
          this.getSelectedOptions(e.detail.data.variantSelected, configurableCombinations[sku]);
        }
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
