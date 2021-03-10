import { getStorageInfo, setStorageInfo } from '../../../js/utilities/sessionStorage';
import { getUserDetails } from './wishlist-data-helper';
import dispatchCustomEvent from '../../../js/utilities/events';

/**
 * Utility function to get wishlist storage key.
 */
function getWishListStorageKey() {
  return 'wishlist_data';
}

/**
 * Utility function to add a product to wishlist for guest users.
 */
function addProductToWishListForGuestUsers(productData) {
  const storageKey = getWishListStorageKey();
  // Get the existing data.
  let existing = getStorageInfo(storageKey);

  // If no existing data, create an array.
  existing = existing || {};

  // Add new data to storage.
  existing[productData.sku] = productData;

  // Save back to storage.
  setStorageInfo(existing, getWishListStorageKey());
}

/**
 * Utility function to add a product to wishlist.
 */
function addProductToWishList(productData) {
  // For Guest users.
  if (!getUserDetails().id) {
    addProductToWishListForGuestUsers(productData);
  }

  dispatchCustomEvent('productAddedToWishlist', { data: productData, addedInWishList: true });
}

/**
 * Utility function to remove a product from wishlist for guest users.
 */
function removeProductFromWishListForGuestUsers(productSku) {
  const storageKey = getWishListStorageKey();
  // Get the existing data.
  const existing = getStorageInfo(storageKey);

  // Remove the entry for given productSku from existing storage data.
  delete existing[productSku];

  // Save back to storage.
  setStorageInfo(existing, getWishListStorageKey());
}

/**
 * Utility function to remove a product from wishlist.
 */
function removeProductFromWishList(productSku) {
  // For Guest users.
  if (!getUserDetails().id) {
    removeProductFromWishListForGuestUsers(productSku);
  }

  dispatchCustomEvent('productRemovedFromWishlist', { data: productSku, addedInWishList: false });
}

/**
 * Utility function to prepare product details for wishlist.
 */
function prepareProductDetailsForWishList(productSku) {
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
}

export {
  getWishListStorageKey,
  addProductToWishList,
  removeProductFromWishList,
  prepareProductDetailsForWishList,
};
