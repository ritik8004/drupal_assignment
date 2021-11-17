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
 * Returns the wishlist info local storage expiration time for guest users.
 *
 * @returns integer
 *  Time in seconds format.
 */
export const getWishlistInfoStorageExpirationForGuest = () => ((typeof drupalSettings.wishlist.config !== 'undefined'
  && typeof drupalSettings.wishlist.config.wishlistinfoStorageExpirationForGuest !== 'undefined')
  ? parseInt(drupalSettings.wishlist.config.wishlistinfoStorageExpirationForGuest, 10)
  : 0);

/**
 * Returns the wishlist info local storage expiration time for logged in users.
 *
 * @returns integer
 *  Time in seconds format.
 */
export const getWishlistInfoStorageExpirationForLoggedIn = () => ((typeof drupalSettings.wishlist.config !== 'undefined'
  && typeof drupalSettings.wishlist.config.wishlistinfoStorageExpirationForLoggedIn !== 'undefined')
  ? parseInt(drupalSettings.wishlist.config.wishlistinfoStorageExpirationForLoggedIn, 10)
  : 0);
