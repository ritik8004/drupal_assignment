import { getStorageInfo, setStorageInfo } from './storage';
import { hasValue } from './conditionsUtility';

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
 * Return the sku info from wishlist if available.
 *
 * @returns {string}
 *  Sku to get information for.
 */
export const getWishListDataForSku = (sku) => {
  // Get existing wishlist data from storage.
  const wishListItems = getWishListData();

  // Return null if product sku is not in existing data.
  if (!wishListItems
    || !Object.prototype.hasOwnProperty.call(wishListItems, sku)) {
    return null;
  }

  return wishListItems[sku];
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
 * This function is to check the given sku data in wishlist and return
 * the child variant from product information based on selected options.
 *
 * @param {string} sku
 *  Product SKU.
 * @param {object} productData
 *  Configurable product information.
 *
 * @returns {bool|string}
 *  False or variant sku.
 */
export const getFirstChildWithWishlistData = (sku, productData) => {
  // Get the sku information from the wishlist data.
  const skuData = getWishListDataForSku(sku);
  // Retrun if no options data available with the sku in wishlist.
  if (!skuData || !hasValue(skuData.options)) {
    return false;
  }

  const configurableAttributes = productData.configurable_attributes;
  const skuAttributesOptionData = {};
  // Prepare attribute options data in key value pair in format
  // of { attribute_code: attribute_option_value }. As we save
  // { attribute_id: attribute_option_value } format with wishlist
  // storage data so we need to update in desired format to prepare
  // attribute combination to identify the correct variant.
  Object.keys(configurableAttributes).forEach((attributeCode) => {
    const attributeData = configurableAttributes[attributeCode];
    if (typeof attributeData.is_pseudo_attribute !== 'undefined'
      && attributeData.is_pseudo_attribute) {
      skuAttributesOptionData[attributeCode] = attributeData.values[0].value;
      return;
    }

    const attributeValueFromSku = skuData.options.find(
      (option) => ((option.id === attributeData.id) ? option.value : false),
    );
    if (attributeValueFromSku) {
      skuAttributesOptionData[attributeCode] = attributeValueFromSku.value;
    }
  });

  // Return if we don't find any available attributes.
  if (!hasValue(skuAttributesOptionData)) {
    return false;
  }

  // Now as we have attribute code and values, we need to create
  // a combination string to identify the variant in combination
  // by_attribute product data array. The format of attribute
  // combination string is `key|value||`.
  let skuAttributeCombination = '';
  Object.entries(skuAttributesOptionData).forEach((data) => {
    const [key, value] = data;
    skuAttributeCombination = `${skuAttributeCombination}${key}|${value}||`;
  });

  // Check if we have a valid combination string and a variant is available
  // with that key in given product data information. If so return variant sku.
  const configurableCombinations = productData.configurable_combinations;
  if (skuAttributeCombination !== ''
    && typeof configurableCombinations.by_attribute[skuAttributeCombination] !== 'undefined') {
    return configurableCombinations.by_attribute[skuAttributeCombination];
  }

  // Return false if we don't find anything.
  return false;
};
