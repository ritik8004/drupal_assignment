import { hasValue } from './conditionsUtility';
import { callMagentoApi } from './requestHelper';
import logger from './logger';

/**
 * Helper function to check if Wishlist feature is enabled.
 */
export const isWishlistEnabled = () => {
  if (typeof drupalSettings.wishlist !== 'undefined'
    && typeof drupalSettings.wishlist.enabled !== 'undefined') {
    return drupalSettings.wishlist.enabled;
  }

  return false;
};

/**
 * Helper function to check if isWishlistPage
 * exists in given object.
 */
export const isWishlistPage = (extraInfo) => {
  if (!isWishlistEnabled()) {
    return false;
  }

  return (typeof extraInfo !== 'undefined'
    && typeof extraInfo.isWishlistPage !== 'undefined'
    && extraInfo.isWishlistPage);
};

/**
 * Helper function to check if curren page is not a wishlist
 * share page and sharing wishlist is enabled.
 */
export const isShareWishlistPage = () => (typeof drupalSettings.wishlist !== 'undefined'
  && typeof drupalSettings.wishlist.context !== 'undefined'
  && drupalSettings.wishlist.context === 'share');

/**
 * Helper function to check sharing wishlist is enabled.
 */
export const isShareWishlistEnabled = () => {
  // Disable share wishlist link if we are on shared wishlist page.
  if (isShareWishlistPage()) {
    return false;
  }

  // Check in the drupal settings if wishlist share is enabled.
  if (typeof drupalSettings.wishlist !== 'undefined'
    && typeof drupalSettings.wishlist.config !== 'undefined'
    && typeof drupalSettings.wishlist.config.enabledShare !== 'undefined') {
    return drupalSettings.wishlist.config.enabledShare;
  }

  return false;
};

/**
 * Returns the wishlist info local storage expiration time for guest users.
 *
 * @returns integer
 *  Time in seconds format.
 */
export const getWishlistInfoStorageExpirationForGuest = () => ((typeof drupalSettings.wishlist.config !== 'undefined'
  && typeof drupalSettings.wishlist.config.localStorageExpirationForGuest !== 'undefined')
  ? parseInt(drupalSettings.wishlist.config.localStorageExpirationForGuest, 10)
  : 0);

/**
 * Returns the wishlist info local storage expiration time for logged in users.
 *
 * @returns integer
 *  Time in seconds format.
 */
export const getWishlistInfoStorageExpirationForLoggedIn = () => ((typeof drupalSettings.wishlist.config !== 'undefined'
  && typeof drupalSettings.wishlist.config.localStorageExpirationForLoggedIn !== 'undefined')
  ? parseInt(drupalSettings.wishlist.config.localStorageExpirationForLoggedIn, 10)
  : 0);

/**
 * Returns the attribute options for configurable product.
 *
 * @param {array} configurableCombinations
 *  Array of configurable combinations of current product.
 * @param {string} skuItemCode
 *  Sku code of main parent product.
 * @param {string} variant
 *  Sku code of variant.
 *
 * @returns array
 *  Attribute options of configurabel product.
 */
export const getAttributeOptionsForWishlist = (configurableCombinations, skuItemCode, variant) => {
  // Add configurable options only for configurable product.
  const options = [];
  if (isWishlistEnabled() && configurableCombinations !== ''
    && configurableCombinations[skuItemCode] && variant) {
    Object.keys(configurableCombinations[skuItemCode].bySku[variant]).forEach((key) => {
      const option = {
        option_id: configurableCombinations[skuItemCode].configurables[key].attribute_id,
        option_value: configurableCombinations[skuItemCode].bySku[variant][key],
      };

      // Skipping the psudo attributes.
      if (drupalSettings.psudo_attribute === undefined
        || drupalSettings.psudo_attribute !== option.option_id) {
        options.push(option);
      }
    });
  }
  return options;
};

/**
 * Check if user is anonymous.
 *
 * @returns {bool}
 */
export const isAnonymousUser = () => (drupalSettings.user.uid === 0);

/**
 * Wishlist local storage key for guest users.
 */
export const guestUserStorageKey = () => 'guestUserwishlistInfo';

/**
 * Wishlist local storage key for logged in users.
 */
export const loggedInUserStorageKey = () => 'loggedInUserwishlistInfo';

/**
 * Utility function to get wishlist storage key.
 */
export const getWishListStorageKey = () => (isAnonymousUser()
  ? guestUserStorageKey()
  : loggedInUserStorageKey());

/**
 * Return the current wishlist info if available.
 *
 * @returns {object}
 *  An object of wishlist information.
 */
export const getWishListData = (strgKey) => {
  // Get local storage key for the wishlist.
  const storageKey = hasValue(strgKey) ? strgKey : getWishListStorageKey();

  // Get data from local storage.
  return Drupal.getItemFromLocalStorage(storageKey);
};

/**
 * Utility function to check if product sku is already exist in wishlist.
 */
export const isProductExistInWishList = (productSku) => {
  // Get existing wishlist data from storage.
  const wishListItems = getWishListData();

  // Check if product sku is in existing data.
  if (wishListItems && Object.prototype.hasOwnProperty.call(wishListItems, productSku)) {
    return true;
  }

  return false;
};

/**
 * Return the sku info from wishlist if available.
 *
 * @returns {string}
 *  Sku to get information for.
 */
export const getWishListDataForSku = (sku) => {
  // Get existing wishlist data from storage.
  const wishListItems = getWishListData();

  // Return null if product sku is not in existing data.
  if (!wishListItems
    || !Object.prototype.hasOwnProperty.call(wishListItems, sku)) {
    return null;
  }

  return wishListItems[sku];
};

/**
 * Add wishlist information in the local storage.
 *
 * @param {object} wishListData
 *  An object of wishlist information.
 */
export const addWishListInfoInStorage = (wishListData, strgKey = null) => {
  // Configurable expiration time, by default it is 300s.
  const storageExpireTime = isAnonymousUser()
    ? getWishlistInfoStorageExpirationForGuest()
    : getWishlistInfoStorageExpirationForLoggedIn();

  // Store data to local storage.
  Drupal.addItemInLocalStorage(
    hasValue(strgKey) ? strgKey : getWishListStorageKey(),
    wishListData,
    storageExpireTime,
  );
};

/**
 * This function is to check the given sku data in wishlist and return
 * the child variant from product information based on selected options.
 *
 * @param {string} sku
 *  Product SKU.
 * @param {object} productData
 *  Configurable product information.
 *
 * @returns {bool|string}
 *  False or variant sku.
 */
export const getFirstChildWithWishlistData = (sku, productData) => {
  // Get the sku information from the wishlist data.
  const skuData = getWishListDataForSku(sku);
  // Retrun if no options data available with the sku in wishlist.
  if (!skuData || !hasValue(skuData.options)) {
    return false;
  }

  const configurableAttributes = productData.configurable_attributes;
  const skuAttributesOptionData = {};
  // Prepare attribute options data in key value pair in format
  // of { attribute_code: attribute_option_value }. As we save
  // { attribute_id: attribute_option_value } format with wishlist
  // storage data so we need to update in desired format to prepare
  // attribute combination to identify the correct variant.
  Object.keys(configurableAttributes).forEach((attributeCode) => {
    const attributeData = configurableAttributes[attributeCode];
    if (typeof attributeData.is_pseudo_attribute !== 'undefined'
      && attributeData.is_pseudo_attribute) {
      skuAttributesOptionData[attributeCode] = attributeData.values[0].value;
      return;
    }

    const attributeValueFromSku = skuData.options.find(
      (option) => ((option.id === attributeData.id) ? option.value : false),
    );
    if (attributeValueFromSku) {
      skuAttributesOptionData[attributeCode] = attributeValueFromSku.value;
    }
  });

  // Return if we don't find any available attributes.
  if (!hasValue(skuAttributesOptionData)) {
    return false;
  }

  // Now as we have attribute code and values, we need to create
  // a combination string to identify the variant in combination
  // by_attribute product data array. The format of attribute
  // combination string is `key|value||`.
  let skuAttributeCombination = '';
  Object.entries(skuAttributesOptionData).forEach((data) => {
    const [key, value] = data;
    skuAttributeCombination = `${skuAttributeCombination}${key}|${value}||`;
  });

  // Check if we have a valid combination string and a variant is available
  // with that key in given product data information. If so return variant sku.
  const configurableCombinations = productData.configurable_combinations;
  if (skuAttributeCombination !== ''
    && typeof configurableCombinations.by_attribute[skuAttributeCombination] !== 'undefined') {
    return configurableCombinations.by_attribute[skuAttributeCombination];
  }

  // Return false if we don't find anything.
  return false;
};

/**
 * Utility function to add a product to wishlist for guest users.
 */
export const addProductToWishListForGuestUsers = (productInfo) => {
  // Get existing wishlist data from storage.
  let wishListItems = getWishListData();

  // If no existing data, create an array.
  wishListItems = wishListItems || {};

  // Add new data to storage.
  // We only need to store sku and options and not title.
  wishListItems[productInfo.sku] = {
    sku: productInfo.sku,
    options: productInfo.options,
  };

  // Save back to storage.
  addWishListInfoInStorage(wishListItems);

  // Always return a Promise object.
  return new Promise((resolve) => {
    resolve({ data: { status: true } });
  });
};

/**
 * Get the wishlist information from the backend using API.
 *
 * @returns {Promise<object>}
 *   A promise object.
 */
export const getWishlistFromBackend = async () => {
  // Call magento api to get the wishlist items of current logged in user.
  const response = await callMagentoApi('/V1/wishlist/me/items', 'GET');
  if (hasValue(response.data)) {
    if (hasValue(response.data.error)) {
      logger.warning('Error getting wishlist items. Response: @response', {
        '@response': JSON.stringify(response.data),
      });
    }
  }

  // Return response to perform necessary operation
  // from where this function called.
  return response;
};

/**
 * Adds/removes products from wishlist in backend using API.
 *
 * @param {object} data
 *   The data object to send in the API call.
 *
 * @returns {Promise<object>}
 *   A promise object.
 */
export const addRemoveWishlistItemsInBackend = async (data, action) => {
  // Early return is no action is provided.
  if (typeof action === 'undefined') {
    return null;
  }

  let requestMethod = null;
  let requestUrl = null;
  let itemData = null;

  switch (action) {
    case 'addWishlistItem': {
      requestMethod = 'POST';
      requestUrl = '/V1/wishlist/me/item/add';

      // Prepare sku options if available to push in backend api.
      const skuOptions = [];
      if (typeof data.options !== 'undefined'
        && data.options.length > 0) {
        data.options.forEach((option) => {
          skuOptions.push({
            id: option.option_id,
            value: option.option_value,
          });
        });
      }

      // Prepare wishlist item to push in backend api.
      itemData = {
        items: [
          {
            sku: data.sku,
            options: skuOptions,
          },
        ],
      };
      break;
    }

    case 'removeWishlistItem':
      requestMethod = 'DELETE';
      requestUrl = `/V1/wishlist/me/item/${data.wishlistItemId}/delete`;
      break;

    case 'mergeWishlistItems':
      requestMethod = 'POST';
      requestUrl = '/V1/wishlist/me/item/add';
      itemData = { items: data };
      break;

    default:
      logger.critical('Endpoint does not exist for action: @action.', {
        '@action': action,
      });
  }

  // Call magento backend with the api details.
  const response = await callMagentoApi(requestUrl, requestMethod, itemData);

  // Log if there are errors in the response.
  if (hasValue(response.data) && hasValue(response.data.error)) {
    logger.warning('Error adding item to wishlist. Post: @post, Response: @response', {
      '@post': JSON.stringify(itemData),
      '@response': JSON.stringify(response.data),
    });
  }

  return response;
};

/**
 * Utility function to add a product to wishlist for logged in users.
 */
export const addProductToWishListForLoggedInUsers = (productInfo) => (
  addRemoveWishlistItemsInBackend(
    productInfo,
    'addWishlistItem',
  ));

/**
 * Utility function to add a product to wishlist.
 */
export const addProductToWishList = (productInfo) => {
  // For anonymouse users.
  if (isAnonymousUser()) {
    return addProductToWishListForGuestUsers(productInfo);
  }

  // For logged in users.
  return addProductToWishListForLoggedInUsers(productInfo);
};

/**
 * Utility function to add a product to wishlist for logged in users.
 */
export const removeProductFromWishListForLoggedInUsers = (productInfo) => (
  addRemoveWishlistItemsInBackend(
    productInfo,
    'removeWishlistItem',
  ));

/**
 * Utility function to remove a product from wishlist.
 */
export const removeProductFromWishList = (productSku) => {
  // Get existing wishlist data from storage.
  const wishListItems = getWishListData();

  // Return is no existing data found.
  if (!wishListItems
    || typeof wishListItems[productSku] === 'undefined') {
    logger.warning('Product not found in local storage. Product SKU: @productSku. Wishlist Data: @wishlistData.', {
      '@productSku': productSku,
      '@wishlistData': JSON.stringify(wishListItems),
    });
    // Always return a Promise object.
    return new Promise((resolve) => {
      resolve(null);
    });
  }

  // For guest users.
  if (isAnonymousUser()) {
    // Always return a Promise object.
    return new Promise((resolve) => {
      resolve({ data: { status: true } });
    });
  }

  // For logged in users, check if product's wishlistItemId exist.
  // If not return Promise object with null response.
  if (typeof wishListItems[productSku].wishlistItemId === 'undefined') {
    // Always return a Promise object.
    return new Promise((resolve) => {
      resolve(null);
    });
  }

  // If wishlistItemId exists, do remove product from backend
  // and return the response of API call.
  return removeProductFromWishListForLoggedInUsers({
    wishlistItemId: wishListItems[productSku].wishlistItemId,
  });
};

/**
 * Utility function to prepare product details for wishlist.
 */
export const getWishlistLabel = () => (drupalSettings.wishlist.wishlist_label ? drupalSettings.wishlist.wishlist_label : '');

/**
 * Utility function to check if config for removing product from
 * wishlist after product added to cart is set to true.
 */
export const removeFromWishlistAfterAddtocart = () => {
  if (drupalSettings.wishlist && drupalSettings.wishlist.config
    && drupalSettings.wishlist.config.removeAfterAddtocart) {
    return drupalSettings.wishlist.config.removeAfterAddtocart;
  }
  return false;
};

/**
 * Utility function to get wishlist notification time.
 */
export const getWishlistNotificationTime = () => (3000);/**
 * Get the shared wishlist information from the backend using API.
 *
 * @returns {Promise<object>}
 *   A promise object.
 */
export const getSharedWishlistFromBackend = () => {
  // Call magento api to get the wishlist items from sharing code.
  const response = callMagentoApi(`/V1/wishlist/code/${drupalSettings.wishlist.sharedCode}/items`, 'GET');
  if (hasValue(response.data)) {
    if (hasValue(response.data.error)) {
      logger.warning('Error getting wishlist items. Response: @response', {
        '@response': JSON.stringify(response.data),
      });
    }
  }

  // Return response to perform necessary operation
  // from where this function called.
  return response;
};

/**
 * Get the raw wishlist information from the backend using API.
 *
 * @returns {Promise<object>}
 *   A promise object.
 */
export const getWishlistInfoFromBackend = async () => {
  // Call magento api to get the wishlist items of current logged in user.
  const response = await callMagentoApi('/V1/wishlist/me/get', 'GET');
  if (hasValue(response.data)) {
    if (hasValue(response.data.error)) {
      logger.warning('Error getting wishlist items. Response: @response', {
        '@response': JSON.stringify(response.data),
      });
    }
  }

  // Return response to perform necessary operation
  // from where this function called.
  return response;
};

/**
 * Helper function to check the stock status of the product with search
 * results and if user logged in then we will check in the local storage
 * wishlist data as well for backend stock status.
 */
export const getWishlistItemInStockStatus = (searchResult) => {
  // For logged in users we will check in wishlist local storage data.
  // If the 'is_in_stock' key is set for stock info, we will use that.
  if (!isAnonymousUser()) {
    const skuInfo = getWishListDataForSku(searchResult.sku);
    if (skuInfo !== null) {
      return (typeof skuInfo.inStock !== 'undefined'
        && skuInfo.inStock);
    }
  }

  // For anonymous user we check stock status in given search result record.
  if (typeof searchResult.stock !== 'undefined') {
    return (searchResult.stock !== 0);
  }

  // Return false by default.
  return true;
};

/**
 * Remove products from the wishlist which are not available in
 * given products array. We are using this function to show the
 * notification message on my wishlist page when products in
 * wishlist does not found in algolia search results.
 */
export const removeDiffFromWishlist = (productsObj) => {
  // Return if no products provided for diff check and remove.
  if (!hasValue(productsObj)) {
    return;
  }

  // Get existing wishlist data from storage.
  const wishListItems = getWishListData();

  if (wishListItems) {
    Object.keys(wishListItems).forEach((keySku) => {
      // Check if wishlist product sku exist in given
      // products array and if not, we will remove that
      // product from the wishlist of users.
      if (!productsObj.find(
        (productObj) => productObj.sku === keySku,
      )) {
        // Call remove product from wishlist function. This
        // will handle removing product from wishlist for both
        // anonymous and logged in users.
        removeProductFromWishList(keySku).then((response) => {
          if (hasValue(response)
            && hasValue(response.data)
            && hasValue(response.data.status)
            && response.data.status) {
            // Remove the entry for given product sku from existing storage data.
            delete wishListItems[keySku];

            // Save back to storage.
            addWishListInfoInStorage(wishListItems);
          }
        });
      }
    });
  }
};
