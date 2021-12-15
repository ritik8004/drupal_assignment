import dispatchCustomEvent from '../../../js/utilities/events';
import { getStorageInfo, setStorageInfo } from '../../../js/utilities/storage';
import { getWishlistInfoStorageExpirationForGuest } from '../../../js/utilities/wishlistHelper';

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
 * Return the wishlist info available in local storage.
 * Return null if wishlist info in local storage is expired.
 *
 * @returns {object}
 *  An object of wishlist information.
 */
export const getWishListInfoForGuestUsers = () => {
  // Get local storage key for the wishlist.
  const storageKey = getWishListStorageKey();

  // Get data from local storage.
  const wishListInfo = getStorageInfo(storageKey);

  // If data is not available in storage, we flag it to check/fetch from api.
  if (!wishListInfo || !wishListInfo.infoData) {
    return null;
  }

  // Configurable expiration time, by default it is 300s.
  const storageExpireTime = getWishlistInfoStorageExpirationForGuest();
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
 * Return the current wishlist info if available.
 *
 * @returns {object}
 *  An object of wishlist information.
 */
export const getWishListData = () => {
  // For Guest users.
  if (isAnonymousUser()) {
    return getWishListInfoForGuestUsers();
  }

  // @todo: we need to work on for logged in users.
  return null;
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
};

/**
 * Utility function to add a product to wishlist.
 */
export const addProductToWishList = (productInfo, setWishListStatus) => {
  // For Guest users.
  if (isAnonymousUser()) {
    addProductToWishListForGuestUsers(productInfo);
  }

  // @todo: we need to work on for logged in users.
  if (setWishListStatus) {
    setWishListStatus(true);
  }
  dispatchCustomEvent('productAddedToWishlist', { productInfo, addedInWishList: true });
};

/**
 * Utility function to remove a product from wishlist for guest users.
 */
export const removeProductFromWishListForGuestUsers = (productSku) => {
  // Get existing wishlist data from storage.
  const wishListItems = getWishListData();

  // Return is no existing data found.
  if (!wishListItems) {
    return;
  }

  // Remove the entry for given productSku from existing storage data.
  delete wishListItems[productSku];

  // Save back to storage.
  addWishListInfoInStorage(wishListItems);
};

/**
 * Utility function to remove a product from wishlist.
 */
export const removeProductFromWishList = (productSku, setWishListStatus) => {
  // For Guest users.
  if (isAnonymousUser()) {
    removeProductFromWishListForGuestUsers(productSku);
  }

  // @todo: we need to work on for logged in users.
  setWishListStatus(false);
  dispatchCustomEvent('productRemovedFromWishlist', { sku: productSku, addedInWishList: false });
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
