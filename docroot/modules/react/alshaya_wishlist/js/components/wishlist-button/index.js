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
    };
  }

  componentDidMount = () => {
    const { skuCode } = this.state;
    const { context } = this.props;
    // @todo: we need to listen wishlist load event that
    // will trigger from header wishlist component after
    // wishlist data are fetched from MDC on page load
    // for logged in user.
    // Check if product already exist in wishlist, and
    // set the status for the sku.
    if (isProductExistInWishList(skuCode)) {
      this.updateWishListStatus(true);
    }

    // Rendering wishlist button as per sku variant info.
    // Event listener is not required for new pdp.
    if (context !== 'magazinev2') {
      document.addEventListener('onSkuVariantSelect', this.updateProductInfoData, false);
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
   * Process selected product attributes and save into storage.
   */
  processProductData = () => {
    let variants = null;
    const dataObj = {};
    const { configurableCombinations } = drupalSettings;
    const { context, sku } = this.props;
    const options = [];
    let currentSku = sku;
    const productKey = context === 'matchback' ? 'matchback' : 'productInfo';
    const productInfo = drupalSettings[productKey];
    let form = null;

    // Get sku base form element from page html.
    // Except new pdp magazine v2 layouts.
    if (context !== 'magazinev2') {
      const elementSelector = context === 'pdp' ? '.wishlist-pdp-full' : `.wishlist-pdp-${context}`;
      // For matchback, get html content from data attribute
      const selectedEelement = context === 'matchback'
        ? document.querySelector(`[data-matchback-sku="${sku}"]`)
        : document.querySelector(elementSelector);
      // Render sku base form for all layouts.
      form = selectedEelement !== null
        ? selectedEelement.closest('article').querySelector('.sku-base-form')
        : null;
    }

    // Get variant sku from selected variant attribute.
    const variantSku = context !== 'magazinev2' && form !== null
      ? form.querySelector('[name="selected_variant_sku"]').value
      : document.getElementById('pdp-add-to-cart-form-main').getAttribute('variantselected');

    // For configurable skus, load attribute options.
    if (configurableCombinations && configurableCombinations[sku]) {
      const attributes = configurableCombinations[sku].configurables;
      Object.keys(attributes).forEach((key) => {
        // Skipping the pseudo attributes.
        if (drupalSettings.psudo_attribute === undefined
          || drupalSettings.psudo_attribute !== attributes[key].attribute_id) {
          // Getting active option value from html selector of pdp.
          // Html selectors are different for magazine v2.
          const option = {
            option_id: attributes[key].code,
            option_value: context === 'magazinev2'
              ? document.querySelector(`#pdp-add-to-cart-form-main #${key}`).querySelectorAll('.active')[0].value
              : form.querySelector(`[data-configurable-code="${key}"]`).value,
          };
          options.push(option);
        }
      });
      if (productInfo[sku] && productInfo[sku].variants) {
        variants = productInfo[sku].variants;
        currentSku = productInfo[sku].variants[variantSku].parent_sku;
      }
    } else if (productInfo[sku] && productInfo[sku].group) {
      currentSku = variantSku;
      variants = productInfo[sku].group;
    }
    // Save required product attributes and return.
    dataObj.sku = currentSku;
    dataObj.title = this.getProductTitle(currentSku, variantSku, variants, productInfo);
    dataObj.options = options;
    return dataObj;
  }

  /**
   * Fetch product title for main sku or variant selected.
   */
  getProductTitle = (currentSku, variantSku, variants, productInfo) => {
    const { sku } = this.props;
    if (drupalSettings.productInfo[currentSku]) {
      return drupalSettings.productInfo[currentSku].cart_title;
    } if (productInfo[sku] && variants !== null) {
      return variants[variantSku].cart_title;
    }
    return '';
  }

  /**
   * Add or remove product from the wishlist.
   */
  toggleWishlist = () => {
    const { addedInWishList, skuCode } = this.state;
    const { title, context } = this.props;
    let productData = {};
    // If product already in wishlist remove this else add.
    if (addedInWishList) {
      removeProductFromWishList(skuCode, this.updateWishListStatus);
      return;
    }
    const pdpLayouts = ['pdp', 'magazinev2', 'modal', 'matchback'];
    if (pdpLayouts.includes(context)) {
      productData = this.processProductData();
    } else {
      productData = {
        sku: skuCode,
        title,
      };
    }
    if (Object.keys(productData).length !== 0) {
      addProductToWishList(productData, this.updateWishListStatus);
    }
  }

  /**
   * Update product info state as per variant selection.
   */
  updateProductInfoData = (e) => {
    if (e.detail && e.detail.data.sku) {
      const variantSku = e.detail.data.sku;
      this.setState({
        skuCode: variantSku,
      });
      this.updateWishListStatus(isProductExistInWishList(variantSku));
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
    const pdpLayouts = ['pdp', 'magazinev2', 'modal', 'matchback'];
    if (pdpLayouts.includes(context)) {
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
