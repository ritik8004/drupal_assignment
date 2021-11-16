/**
 * Helper function to check if Wishlist feature is enabled.
 */
const isWishlistEnabled = () => {
  if (typeof drupalSettings.wishlist !== 'undefined'
    && typeof drupalSettings.wishlist.enabled !== 'undefined') {
    return drupalSettings.wishlist.enabled;
  }

  return false;
};

export default isWishlistEnabled;
