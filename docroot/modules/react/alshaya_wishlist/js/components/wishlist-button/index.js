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
    if (context === 'pdp') {
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
    const { productInfo, configurableCombinations } = drupalSettings;
    const { context, sku } = this.props;
    let currentSku = sku;

    // Get sku base form element from page html.
    // @todo check data attribute for modal view.
    const form = document.querySelector('.sku-base-form');
    // Get variant sku from selected variant attribute.
    const variantSku = context === 'magazinev2'
      ? document.getElementById('pdp-add-to-cart-form-main').getAttribute('variantselected')
      : form.querySelector('[name="selected_variant_sku"]').value;

    // For configurable skus, load attribute options.
    if (configurableCombinations) {
      const attributes = configurableCombinations[sku].configurables;
      const options = [];
      Object.keys(attributes).forEach((key) => {
        // Skipping the pseudo attributes.
        if (drupalSettings.psudo_attribute === undefined
          || drupalSettings.psudo_attribute !== attributes[key].attribute_id) {
          // Getting active option value from html selector.
          // Html selectors are different for magazine v2 and old pdp.
          const option = {
            option_id: attributes[key].code,
            option_value: context === 'magazinev2'
              ? document.querySelector(`#pdp-add-to-cart-form-main #${key}`).querySelectorAll('.active')[0].value
              : form.querySelector(`[data-configurable-code="${key}"]`).value,
          };
          options.push(option);
        }
      });
      dataObj.options = options;
      currentSku = productInfo[sku].variants[variantSku].parent_sku;
      if (productInfo[sku] && productInfo[sku].variants) {
        variants = productInfo[sku].variants;
      }
    } else if (productInfo[sku] && productInfo[sku].group) {
      currentSku = variantSku;
      variants = productInfo[sku].group;
    }

    // Save required product attributes and return.
    dataObj.sku = currentSku;
    dataObj.title = this.getProductTitle(currentSku, variantSku, variants);
    return dataObj;
  }

  /**
   * Fetch product title for main sku or variant selected.
   */
  getProductTitle = (currentSku, variantSku, variants) => {
    const { productInfo } = drupalSettings;
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
    const { addedInWishList, skuCode } = this.state;
    const { title, context } = this.props;
    let productData = {};
    // If product already in wishlist remove this else add.
    if (addedInWishList) {
      removeProductFromWishList(skuCode, this.updateWishListStatus);
      return;
    }
    if (context === 'pdp' || context === 'magazinev2') {
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
    if (e.detail && e.detail.data !== '') {
      const variantInfo = e.detail.data;
      const variantSku = variantInfo.parent_sku ? variantInfo.parent_sku : variantInfo.sku;
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
    const wishlistLabel = getWishlistLabel();
    const wishListButtonClass = addedInWishList ? `${classPrefix} in-wishlist` : classPrefix;
    const buttonText = addedInWishList ?
      Drupal.t('Added to @wishlist_label', { '@wishlist_label': wishlistLabel }, { context: 'wishlist' }) :
      Drupal.t('Add to @wishlist_label', { '@wishlist_label': wishlistLabel }, { context: 'wishlist' });

    return (
      <div
        className={wishListButtonClass}
        onClick={() => this.toggleWishlist()}
      >
        {Drupal.t(buttonText, {}, { context: 'wishlist' })}
      </div>
    );
  }
}

export default WishlistButton;
