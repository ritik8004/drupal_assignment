import Cookies from 'js-cookie';
import {
  isAnonymousUser,
  getWishListData,
  getWishListDataForSku,
  addWishListInfoInStorage,
} from '../../../js/utilities/wishlistHelper';
import { callMagentoApi } from '../../../js/utilities/requestHelper';
import logger from '../../../js/utilities/logger';
import { hasValue } from '../../../js/utilities/conditionsUtility';

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
      if (data.options.length > 0) {
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
 * Get the shared wishlist information from the backend using API.
 *
 * @returns {Promise<object>}
 *   A promise object.
 */
export const getSharedWishlistFromBackend = async () => {
  // Call magento api to get the wishlist items from sharing code.
  // @todo: need to replace the request URL with get shared wishlist
  // api, once available in QA environment.
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
 * Utility function to get wishlist notification time.
 */
export const getWishlistNotificationTime = () => (3000);

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

export const getWishlistShareCode = () => {
  // First check if the wishlist share code available in cookies.
  if (Cookies.get('wlsShareCode')) {
    return Cookies.get('wlsShareCode');
  }

  // If we don't have one available in cookies, we need to get this
  // from the backend via API call and set that in cookies.
  let wlsShareCode = null;

  // Call magento api to get the wishlist details of current logged in user.
  getWishlistInfoFromBackend().then((response) => {
    if (hasValue(response.data)) {
      if (hasValue(response.data.status)
        && hasValue(response.data.sharing_code)) {
        wlsShareCode = response.data.sharing_code;
        Cookies.set('wlsShareCode', wlsShareCode, { expires: 1, path: '/' });
      }
    }
  });

  // Return wlsShareCode, so,
  // If one is available in backend, that will be returned
  // else null value will be returned.
  return wlsShareCode;
};

/**
 * Helper function to generate the wishlist share link with wishlist data.
 */
export const getWishlistShareLink = () => {
  // Return if user is anonymous.
  if (isAnonymousUser()) {
    return null;
  }

  // Get wishlist sharing code.
  const sharCode = getWishlistShareCode();
  // Return if sharing code is null.
  if (!sharCode) {
    return null;
  }

  // Prepare the share wishlist url with wishlist sharing code.
  const encodedShareCode = btoa(sharCode);
  return Drupal.url.toAbsolute(`wishlist/share?data=${encodedShareCode}`);
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
          if (typeof response.data !== 'undefined'
            && typeof response.data.status !== 'undefined'
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
