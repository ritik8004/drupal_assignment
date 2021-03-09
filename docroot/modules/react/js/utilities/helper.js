/**
 * Helper function to check if WishList is enabled.
 */
export default function isWishListEnabled() {
  let enabled = false;
  if (typeof drupalSettings.wishlist !== 'undefined'
    && ({}).hasOwnProperty.call(drupalSettings.wishlist, 'enabled')) {
    enabled = drupalSettings.wishlist.enabled;
  }

  return enabled;
}
