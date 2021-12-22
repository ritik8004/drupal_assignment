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
export const isWishlistPage = (extraInfo) => ((typeof extraInfo !== 'undefined'
    && typeof extraInfo.isWishlistPage !== 'undefined'
    && extraInfo.isWishlistPage));

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
 * Utility function to add inline loader.
 */
export const addInlineLoader = (selector) => {
  const element = document.querySelectorAll(selector);

  if (element.length > 0) {
    element.forEach((el) => {
      el.classList.add('loading');
    });
  }
};

/**
 * Utility function to hide inline loader.
 */
export const removeInlineLoader = (selector) => {
  const element = document.querySelectorAll(selector);

  if (element.length > 0) {
    element.forEach((el) => {
      el.classList.remove('loading');
    });
  }
};
