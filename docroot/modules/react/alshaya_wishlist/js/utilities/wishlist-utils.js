import { getStorageInfo, setStorageInfo } from '../../../js/utilities/storage';
import {
  getWishlistInfoStorageExpirationForGuest,
  getWishlistInfoStorageExpirationForLoggedIn,
} from '../../../js/utilities/wishlistHelper';
import { callMagentoApi } from '../../../js/utilities/requestHelper';
import logger from '../../../js/utilities/logger';
import { hasValue } from '../../../js/utilities/conditionsUtility';

/**
 * Check if user is anonymous.
 *
 * @returns {bool}
 */
export const isAnonymousUser = () => (drupalSettings.user.uid === 0);

/**
 * Utility function to get wishlist storage key.
 */
export const getWishListStorageKey = () => 'wishlistInfo';

/**
 * Return the current wishlist info if available.
 *
 * @returns {object}
 *  An object of wishlist information.
 */
export const getWishListData = () => {
  // Get local storage key for the wishlist.
  const storageKey = getWishListStorageKey();

  // Get data from local storage.
  const wishListInfo = getStorageInfo(storageKey);

  // If data is not available in storage, we flag it to check/fetch from api.
  if (!wishListInfo || !wishListInfo.infoData) {
    return null;
  }

  // Configurable expiration time, by default it is 300s.
  const storageExpireTime = isAnonymousUser()
    ? getWishlistInfoStorageExpirationForGuest()
    : getWishlistInfoStorageExpirationForLoggedIn();
  const expireTime = storageExpireTime * 1000;
  const currentTime = new Date().getTime();

  // If data is expired, we flag it to check/fetch from api.
  if ((currentTime - wishListInfo.last_update) > expireTime) {
    // Empty wishlist info from local storage.
    setStorageInfo({}, getWishListStorageKey());
    return null;
  }

  return wishListInfo.infoData;
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
 * Add wishlist information in the local storage.
 *
 * @param {object} wishListData
 *  An object of wishlist information.
 */
export const addWishListInfoInStorage = (wishListData) => {
  const wishListInfo = {
    infoData: wishListData,
    // Adding current time to storage to know the last time data updated.
    last_update: new Date().getTime(),
  };

  // Store data to local storage.
  setStorageInfo(wishListInfo, getWishListStorageKey());
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
  wishListItems[productInfo.sku] = productInfo;

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
 * @param {object} data
 *   The data object to send in the API call.
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
 * Helper function to check if Wishlist sharing is enabled.
 */
export const isShareWishlistEnabled = () => {
  if (typeof drupalSettings.wishlist !== 'undefined'
    && typeof drupalSettings.wishlist.config !== 'undefined'
    && typeof drupalSettings.wishlist.config.enabledShare !== 'undefined') {
    return drupalSettings.wishlist.config.enabledShare;
  }

  return false;
};

/**
 * Helper function to generate the wishlist share link with wishlist data.
 */
export const getWishlistShareLink = () => {
  // Get existing wishlist data from storage.
  const wishListItems = getWishListData();

  // Return if no existing data found.
  if (!wishListItems) {
    return null;
  }

  // Prepare the share wishlist url if items are available.
  // @todo: reduce the length of the URL.
  const shareItemsParams = btoa(JSON.stringify(wishListItems));
  return Drupal.url.toAbsolute(`wishlist/share?data=${shareItemsParams}`);
};

/**
 * Helper function to check if wishlist merge is enabled. We get this true
 * once when anonymouse user logged in with some existing data of
 * wishlist in local storage. With this flag, we will merge local storage
 * wishlist information to backend via API call.
 */
export const isWishlistMergeEnabled = () => (typeof drupalSettings.wishlist.merge !== 'undefined'
  ? drupalSettings.wishlist.merge
  : null);
