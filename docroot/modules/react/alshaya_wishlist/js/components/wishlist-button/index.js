import React from 'react';
import {
  isProductExistInWishList,
  isAnonymousUser,
  addWishListInfoInStorage,
  getWishListData,
  addProductToWishList,
  removeProductFromWishList,
  getWishlistLabel,
  getWishlistFromBackend,
  removeFromWishlistAfterAddtocart,
} from '../../../../js/utilities/wishlistHelper';
import { hasValue } from '../../../../js/utilities/conditionsUtility';
import dispatchCustomEvent from '../../../../js/utilities/events';
import { addInlineLoader, removeInlineLoader } from '../../../../js/utilities/showRemoveInlineLoader';
import { removeFullScreenLoader, showFullScreenLoader } from '../../../../js/utilities/showRemoveFullScreenLoader';
import getStringMessage from '../../../../js/utilities/strings';

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

    const contextArray = ['pdp', 'modal', 'matchback'];
    if (contextArray.includes(context)) {
      // Set title for sku product on page load.
      const productKey = context === 'matchback' ? 'matchback' : 'productInfo';
      const productInfo = drupalSettings[productKey];
      this.setState({
        title: productInfo[sku].cart_title ? productInfo[sku].cart_title : '',
      });
      // Rendering wishlist button as per sku variant info.
      // Event listener is only required for old pdp, modal and matchback.
      document.addEventListener('onSkuVariantSelect', this.updateProductInfoData, false);
      // Handle wishlist state for item when it is added to cart.
      // We call custom event listener defined in wishlist module.
      // This is only for old pdp, modal and matchback.
      // Check if config for removing product from
      // wishlist after product added to cart is set to true.
      if (removeFromWishlistAfterAddtocart()) {
        document.addEventListener('onProductAddToCart', this.handleProductAddToCart);
      }
    }

    // Handle wishlist state for item when it is added to cart.
    // This event listener if generic and called directly for new pdp.
    // Defining context array for new pdp and other product layout build in react.
    const reactContextArray = ['magazinev2', 'magazinev2-related', 'productDrawer', 'wishlist'];
    // Check if context present in react components context array.
    // Also, check if config for removing product from
    // wishlist after product added to cart is set to true.
    if (reactContextArray.includes(context) && removeFromWishlistAfterAddtocart()) {
      document.addEventListener('product-add-to-cart-success', this.handleProductAddToCart);
    }

    // Check if the context is wishlist page itself and remove product from
    // user's wishlist after added to the cart, regardless the config setting.
    if (context === 'wishlist_page') {
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
      const { context } = this.props;
      if (!hasValue(response)) {
        // Prepare and dispatch an event when product removed from the storage
        // so other components like wishlist header can listen and do the
        // needful.
        dispatchCustomEvent('productRemovedFromWishlist', {
          sku,
          addedInWishList: true,
        });

        // For wishlist page, we remove full loader.
        // For other layouts, we remove inline loader of button.
        if (context === 'wishlist_page') {
          removeFullScreenLoader();
        } else {
          removeInlineLoader('.wishlist-loader .loading');
        }
        return;
      }

      if (hasValue(response.data)
        && hasValue(response.data.status)
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

        // For wishlist page, we remove full loader.
        // For other layouts, we remove inline loader of button.
        if (context === 'wishlist_page') {
          removeFullScreenLoader();
        } else {
          removeInlineLoader('.wishlist-loader .loading');
        }
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
    const { skuCode, addedInWishList } = this.state;
    // Check if item is already in wishlist.
    if (addedInWishList && event.detail) {
      const { context } = this.props;
      const sku = (context === 'magazinev2' || context === 'magazinev2-related')
        ? event.detail.productData.sku : event.detail.sku;
      if (sku === skuCode) {
        this.handleProductRemovalFromWishlist(sku);
      }
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
        options.push(option);
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
  toggleWishlist = (e) => {
    e.preventDefault();
    e.stopPropagation();
    e.persist();

    const {
      addedInWishList, skuCode, options, title,
    } = this.state;
    const { context } = this.props;
    // We don't need inline loader for buttons on wishlist page.
    if (e.currentTarget.classList.length > 0 && context !== 'wishlist_page') {
      // Adding loader icon to wishlist button.
      e.currentTarget.classList.add('loading');
      addInlineLoader('.wishlist-loader .loading');
    }

    // If product already in wishlist remove this else add.
    if (addedInWishList) {
      // Add full screen loader for wishlist page.
      if (context === 'wishlist_page') {
        showFullScreenLoader();
      }
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
        // Get the additional configuration options from props.
        const { extraOptions } = this.props;

        // Prepare event data to pass with productAddedTOwishlist event.
        const eventData = {
          productInfo,
          addedInWishList: true,
          extraOptions,
        };

        // For anonymous user, we update only storage and wishlist button status.
        // We don't need an api call here.
        if (isAnonymousUser()) {
          // Prepare and dispatch an event when product added to the storage
          // so other components like wishlist header can listen and do the
          // needful.
          dispatchCustomEvent('productAddedToWishlist', eventData);

          // Removing loader icon from wishlist button for anonymous user.
          removeInlineLoader('.wishlist-loader.loading');

          // Update the wishlist button state.
          this.updateWishListStatus(true);
        } else {
          // If user is logged in we need to update the products from
          // backend via api and update in local storage to get
          // the wishlist_item_id from the backend that we use while
          // removing the product from backend for logged in user.
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
              dispatchCustomEvent('productAddedToWishlist', eventData);

              // Removing loader icon to wishlist button.
              removeInlineLoader('.wishlist-loader.loading');

              // Update the wishlist button state.
              this.updateWishListStatus(true);
            }
          });
        }
      } else {
        // Removing loader icon from wishlist button if there is an error.
        removeInlineLoader('.wishlist-loader.loading');
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
    // Default wishlist text.
    let buttonTextKey = addedInWishList ? 'remove_from_wishlist' : 'add_to_wishlist';

    // For wishlist page, button text is always remove link.
    if (context === 'wishlist_page') {
      buttonTextKey = 'remove_from_wishlist';
    }

    // If product is already added into wishlist, button is not required on cart page.
    if (addedInWishList && context === 'cart') {
      return null;
    }

    // Display format can be 'link' or 'icon'.
    const formatClass = format || 'icon';
    const classPrefix = `wishlist-${formatClass} ${context} ${position}`;
    const wishListButtonClass = addedInWishList ? 'in-wishlist wishlist-button-wrapper' : 'wishlist-button-wrapper';

    // Wishlist text for PDP layouts.
    const viewModes = ['pdp', 'magazinev2', 'modal', 'matchback', 'productDrawer'];
    if (viewModes.includes(context)) {
      buttonTextKey = addedInWishList ? 'added_to_wishlist' : 'add_to_wishlist';
    }

    // Wishlist text for Basket page.
    if (context === 'cart') {
      // We don't show remove from wishlist on basket page.
      if (addedInWishList) {
        return null;
      }

      // We only need move to wishlist button on cart page.
      buttonTextKey = 'move_to_wishlist';
    }

    return (
      <div
        className={`${wishListButtonClass} wishlist-loader`}
        onClick={(e) => this.toggleWishlist(e)}
      >
        <div className={classPrefix}>
          {getStringMessage(buttonTextKey, { '@wishlist_label': getWishlistLabel() })}
        </div>
      </div>
    );
  }
}

export default WishlistButton;
