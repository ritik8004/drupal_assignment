import dispatchCustomEvent from '../../../js/utilities/events';
import { getStorageInfo, setStorageInfo } from '../../../js/utilities/storage';

/**
 * Check if user is anonymous.
 *
 * @returns {bool}
 */
export const isAnonymousUser = () => (drupalSettings.user.uid === 0);

/**
 * Returns the wishlist info local storage expiration time.
 *
 * @returns integer
 *  Time in seconds format.
 */
export const getWishlistInfoLocalStorageExpiration = () => ((typeof drupalSettings.wishlist.config !== 'undefined'
  && typeof drupalSettings.wishlist.config.wishlistinfoLocalStorageExpiration !== 'undefined')
  ? parseInt(drupalSettings.wishlist.config.wishlistinfoLocalStorageExpiration, 10)
  : 0);

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
export const getWishListInfoAvailableInStorage = () => {
  // Get local storage key for the wishlist.
  const storageKey = getWishListStorageKey();

  // Get data from local storage.
  const wishListInfo = getStorageInfo(storageKey);

  // If data is not available in storage, we flag it to check/fetch from api.
  if (!wishListInfo || !wishListInfo.infoData) {
    return null;
  }

  // Configurable expiration time, by default it is 300s.
  const storageExpireTime = getWishlistInfoLocalStorageExpiration();
  const expireTime = storageExpireTime * 1000;
  const currentTime = new Date().getTime();

  // If data is expired, we flag it to check/fetch from api.
  if ((currentTime - wishListInfo.last_update) > expireTime) {
    return null;
  }

  return wishListInfo.infoData;
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
 * Utility function to check if product sku is already exist in wishlist.
 */
export const isProductExistInWishList = (productSku) => {
  // Get existing wishlist data from storage.
  const existing = getWishListInfoAvailableInStorage();

  // Check if product sku is in existing data.
  if (existing && Object.prototype.hasOwnProperty.call(existing, productSku)) {
    return true;
  }

  return false;
};

/**
 * Utility function to add a product to wishlist for guest users.
 */
export const addProductToWishListForGuestUsers = (productSku) => {
  // Get existing wishlist data from storage.
  let existing = getWishListInfoAvailableInStorage();

  // If no existing data, create an array.
  existing = existing || {};

  // Add new data to storage.
  existing[productSku] = productSku;

  // Save back to storage.
  addWishListInfoInStorage(existing);
};

/**
 * Utility function to add a product to wishlist.
 */
export const addProductToWishList = (productSku, setWishListStatus) => {
  // For Guest users.
  if (isAnonymousUser()) {
    addProductToWishListForGuestUsers(productSku);
  }

  setWishListStatus(true);
  dispatchCustomEvent('productAddedToWishlist', { sku: productSku, addedInWishList: true });
};

/**
 * Utility function to remove a product from wishlist for guest users.
 */
export const removeProductFromWishListForGuestUsers = (productSku) => {
  // Get existing wishlist data from storage.
  const existing = getWishListInfoAvailableInStorage();

  // Return is no existing data found.
  if (!existing) {
    return;
  }

  // Remove the entry for given productSku from existing storage data.
  delete existing[productSku];

  // Save back to storage.
  addWishListInfoInStorage(existing);
};

/**
 * Utility function to remove a product from wishlist.
 */
export const removeProductFromWishList = (productSku, setWishListStatus) => {
  // For Guest users.
  if (isAnonymousUser()) {
    removeProductFromWishListForGuestUsers(productSku);
  }

  setWishListStatus(false);
  dispatchCustomEvent('productRemovedFromWishlist', { sku: productSku, addedInWishList: false });
};

/**
 * Utility function to prepare product details for wishlist.
 */
export const prepareProductDetailsForWishList = (productSku) => {
  // @todo: Need to decide and implement the logic to prepare product data.
  const productDetails = {
    sku: productSku,
    nid: '100',
    alt: 'Benchwright Console Table',
    title: 'Benchwright Console Table',
    original_price: 200,
    final_price: 150,
    url: 'https://www.potterybarn.ae/sites/g/files/bndsjb1296/files/styles/product_zoom_medium_606x504/public/media/website/var/assets/GroupProductImages/benchwright-console-table/201824_0225_benchwright-console-table-rustic-mahogany-z.129478.jpg?itok=PCfrTwVf',
  };

  return productDetails;
};
