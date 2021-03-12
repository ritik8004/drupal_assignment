/**
 * Helper function to get wishlist user details.
 */
function getUserDetails() {
  let wishlistUserDetails = {};
  if (typeof drupalSettings.wishlist !== 'undefined'
    && ({}).hasOwnProperty.call(drupalSettings.wishlist, 'userDetails')) {
    wishlistUserDetails = drupalSettings.wishlist.userDetails;
  }

  return wishlistUserDetails;
}

/**
 * Helper function to get wishlist config.
 */
function getWishListConfig() {
  let wishListConfig = {};
  if (typeof drupalSettings.wishlist !== 'undefined'
    && ({}).hasOwnProperty.call(drupalSettings.wishlist, 'config')) {
    wishListConfig = drupalSettings.wishlist.config;
  }

  return wishListConfig;
}

export {
  getUserDetails,
  getWishListConfig,
};
