import React from 'react';
import {
  isProductExistInWishList,
  addProductToWishList,
  removeProductFromWishList,
  getWishlistLabel,
  isAnonymousUser,
  getWishlistFromBackend,
  addWishListInfoInStorage,
  getWishListData,
} from '../../utilities/wishlist-utils';
import { hasValue } from '../../../../js/utilities/conditionsUtility';
import dispatchCustomEvent from '../../../../js/utilities/events';

class WishlistButton extends React.Component {
  constructor(props) {
    super(props);
    const skuCode = props.skuCode ? props.skuCode : props.sku;
    // Set the products status in state.
    // true: if sku exist in wishlist,
    // false: default, if sku doesn't exist in wishlist.
    // Setting variant selected for current variant.
    // Options are selected attribute options for default product.
    this.state = {
      addedInWishList: isProductExistInWishList(skuCode),
      skuCode,
      options: props.options ? props.options : [],
      title: props.title ? props.title : '',
    };
  }

  componentDidMount = () => {
    const { context, sku } = this.props;
    const { configurableCombinations } = drupalSettings;

    const contextArray = ['pdp', 'modal', 'matchback'];
    if (contextArray.includes(context)) {
      // Set title for simple sku product on page load.
      if (!(this.isConfigurableProduct(sku, configurableCombinations))) {
        const productKey = context === 'matchback' ? 'matchback' : 'productInfo';
        const productInfo = drupalSettings[productKey];
        this.setState({
          title: productInfo[sku].cart_title,
        });
      }
      // Rendering wishlist button as per sku variant info.
      // Event listener is only required for old pdp, modal and matchback.
      document.addEventListener('onSkuVariantSelect', this.updateProductInfoData, false);
      // Handle wishlist state for item when it is added to cart.
      // We call custom event listener defined in wishlist module.
      // This is only for old pdp, modal and matchback.
      document.addEventListener('onProductAddToCart', this.handleProductAddToCart);
    }

    // Handle wishlist state for item when it is added to cart.
    // This event listener if generic and called directly for new pdp.
    // Defining context array for new pdp and other product layout build in react.
    const reactContextArray = ['magazinev2', 'magazinev2-related', 'productDrawer'];
    if (reactContextArray.includes(context)) {
      document.addEventListener('product-add-to-cart-success', this.handleProductAddToCart);
    }

    if (!isAnonymousUser()) {
      // Add event listener for get wishlist load event for logged in user.
      // This will execute when wishlist loaded from the backend
      // and page loads before.
      document.addEventListener('getWishlistFromBackendSuccess', this.checkProductStatusInWishlist, false);
    }
  };

  componentWillUnmount = () => {
    if (!isAnonymousUser()) {
      // Remove event listener bind in componentDidMount.
      document.removeEventListener('getWishlistFromBackendSuccess', this.checkProductStatusInWishlist, false);
    }
  };

  /**
   * Handle item removal from wishlist.
   *
   * @param {string} sku
   *  Sku code of product.
   */
  handleProductRemovalFromWishlist = (sku) => {
    removeProductFromWishList(sku).then((response) => {
      if (typeof response.data !== 'undefined'
        && typeof response.data.status !== 'undefined'
        && response.data.status) {
        // Get existing wishlist data from storage.
        const wishListItems = getWishListData();

        // Remove the entry for given product sku from existing storage data.
        delete wishListItems[sku];

        // Save back to storage.
        addWishListInfoInStorage(wishListItems);

        // Prepare and dispatch an event when product removed from the storage
        // so other components like wishlist header can listen and do the
        // needful.
        dispatchCustomEvent('productRemovedFromWishlist', {
          sku,
          addedInWishList: true,
        });

        // Set the product wishlist status.
        this.updateWishListStatus(false);
      }
    });
  }

  /**
   * This event listener function called when item added to cart.
   *
   * @param {object} event
   *  Event detail containing product data.
   */
  handleProductAddToCart = (event) => {
    if (event.detail && event.detail.productData) {
      this.handleProductRemovalFromWishlist(event.detail.productData.sku);
    }
  }

  /**
   * Check if current product already exist in the wishlist.
   */
  checkProductStatusInWishlist = () => {
    const { skuCode } = this.state;

    // Check if product already exist in wishlist, and
    // set the status for the sku.
    if (isProductExistInWishList(skuCode)) {
      this.updateWishListStatus(true);
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
      this.handleProductRemovalFromWishlist(skuCode);

      // don't execute further if product is removed from the wishlist.
      return;
    }

    // Prepare the product info to store.
    const productInfo = {
      sku: skuCode,
      title,
      options,
    };

    // Add product to the wishlist. For guest users it'll store in local
    // storage and for logged in user this will store in backend using API
    // then will update the local storage as well.
    addProductToWishList(productInfo).then((response) => {
      if (typeof response.data.status !== 'undefined'
        && response.data.status) {
        // Prepare and dispatch an event when product added to the storage
        // so other components like wishlist header can listen and do the
        // needful.
        dispatchCustomEvent('productAddedToWishlist', {
          productInfo,
          addedInWishList: true,
        });

        // If user is logged in we need to update the products from
        // backend via api and update in local storage to get
        // the wishlist_item_id from the backend that we use while
        // removing the product from backend for logged in user.
        if (!isAnonymousUser()) {
          // Load wishlist information from the magento backend.
          getWishlistFromBackend().then((responseData) => {
            if (hasValue(responseData.data.items)) {
              const wishListItems = {};

              // Prepare the information to save in the local storage.
              responseData.data.items.forEach((item) => {
                wishListItems[item.sku] = {
                  sku: item.sku,
                  options: item.options,
                  // We need this for removing the item from the wishlist.
                  wishlistItemId: item.wishlist_item_id,
                  // OOS status of product in backend.
                  inStock: item.is_in_stock,
                };
              });

              // Save back to storage.
              addWishListInfoInStorage(wishListItems);

              // Prepare and dispatch an event when product added to the storage
              // so other components like wishlist header can listen and do the
              // needful.
              dispatchCustomEvent('productAddedToWishlist', {});
            }
          });
        }

        // Update the wishlist button state.
        this.updateWishListStatus(true);
      }
    });
  }

  /**
   * Check if current variant exists in same group of main sku.
   */
  ifExistsInSameGroup = (skuItem) => {
    const { sku, context } = this.props;
    const productKey = context === 'matchback' ? 'matchback' : 'productInfo';
    const productInfo = drupalSettings[productKey];
    let found = false;
    // Check in variant list for grouped configurable product.
    // Else check in item list for grouped simple product.
    if (productInfo[sku].variants) {
      Object.values(productInfo[sku].variants).forEach((variant) => {
        if (variant.parent_sku && variant.parent_sku === skuItem) {
          found = true;
        }
      });
    } else if (productInfo[sku].group) {
      Object.values(productInfo[sku].group).forEach((item) => {
        if (item.sku && item.sku === skuItem) {
          found = true;
        }
      });
    }
    return found;
  }

  /**
   * Update wishlist button state as per variant selection.
   */
  updateProductInfoData = (e) => {
    e.preventDefault();
    if (e.detail) {
      const parentSkuSelected = e.detail.data.sku;
      const { variantSelected, title } = e.detail.data;
      if (parentSkuSelected && variantSelected) {
        const { sku } = this.props;
        if (sku === e.detail.data.sku || this.ifExistsInSameGroup(parentSkuSelected)) {
          const { configurableCombinations } = drupalSettings;
          this.setState({
            skuCode: parentSkuSelected,
            title,
          }, () => {
            // Update wishlist button status for selected variant.
            this.updateWishListStatus(isProductExistInWishList(parentSkuSelected));
            // Get selected attribute options for selected variant.
            if (this.isConfigurableProduct(sku, configurableCombinations)
              && variantSelected) {
              this.getSelectedOptions(variantSelected, configurableCombinations[sku]);
            }
          });
        }
      }
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
