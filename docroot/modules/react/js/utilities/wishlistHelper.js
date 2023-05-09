import { hasValue } from './conditionsUtility';
import logger from './logger';

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
export const isWishlistPage = (extraInfo) => {
  if (!isWishlistEnabled()) {
    return false;
  }

  return (typeof extraInfo !== 'undefined'
    && typeof extraInfo.isWishlistPage !== 'undefined'
    && extraInfo.isWishlistPage);
};

/**
 * Helper function to check if curren page is not a wishlist
 * share page and sharing wishlist is enabled.
 */
export const isShareWishlistPage = () => (typeof drupalSettings.wishlist !== 'undefined'
  && typeof drupalSettings.wishlist.context !== 'undefined'
  && drupalSettings.wishlist.context === 'share');

/**
 * Helper function to check sharing wishlist is enabled.
 */
export const isShareWishlistEnabled = () => {
  // Disable share wishlist link if we are on shared wishlist page.
  if (isShareWishlistPage()) {
    return false;
  }

  // Check in the drupal settings if wishlist share is enabled.
  if (typeof drupalSettings.wishlist !== 'undefined'
    && typeof drupalSettings.wishlist.config !== 'undefined'
    && typeof drupalSettings.wishlist.config.enabledShare !== 'undefined') {
    return drupalSettings.wishlist.config.enabledShare;
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
 *  Attribute options of configurable product.
 */
export const getAttributeOptionsForWishlist = (configurableCombinations, skuItemCode, variant) => {
  // Add configurable options only for configurable product.
  const options = [];
  if (isWishlistEnabled()
    && hasValue(configurableCombinations)
    && hasValue(variant)) {
    if (hasValue(configurableCombinations[skuItemCode])
      && hasValue(configurableCombinations[skuItemCode].bySku
      && hasValue(configurableCombinations[skuItemCode].bySku[variant]))
    ) {
      Object.keys(configurableCombinations[skuItemCode].bySku[variant]).forEach((key) => {
        const option = {
          option_id: configurableCombinations[skuItemCode].configurables[key].attribute_id,
          option_value: configurableCombinations[skuItemCode].bySku[variant][key],
        };
        options.push(option);
      });
    }
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
 * Wishlist local storage key for guest users.
 */
export const guestUserStorageKey = () => 'guestUserwishlistInfo';

/**
 * Wishlist local storage key for logged in users.
 */
export const loggedInUserStorageKey = () => 'loggedInUserwishlistInfo';

/**
 * Utility function to get wishlist storage key.
 */
export const getWishListStorageKey = () => (isAnonymousUser()
  ? guestUserStorageKey()
  : loggedInUserStorageKey());

/**
 * Return the current wishlist info if available.
 *
 * @returns {null|Object}
 *  An object of wishlist information.
 */
export const getWishListData = (strgKey) => {
  // Get local storage key for the wishlist.
  const storageKey = hasValue(strgKey) ? strgKey : getWishListStorageKey();

  // Get data from local storage.
  const wishlistItems = Drupal.getItemFromLocalStorage(storageKey);

  // Check if this returns an object, we will type cast it to an array and
  // and return an array of wishlist items. Else return null.
  return (wishlistItems && typeof wishlistItems === 'object')
    ? Object.values(wishlistItems)
    : null;
};

/**
 * Utility function to check if product sku is already exist in wishlist.
 */
export const isProductExistInWishList = (productSku) => {
  // Get existing wishlist data from storage.
  const wishListItems = getWishListData();

  // Check if product sku is in existing data.
  let ifProductExist = false;
  if (wishListItems) {
    // We need to match the given sku with the sku property for each wishlist
    // item to verify if it exist.
    ifProductExist = wishListItems.find((product) => product.sku === productSku);
  }

  // Return true/false based on the data.
  return hasValue(ifProductExist);
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
  if (!wishListItems) {
    return null;
  }

  // Get the data from the matched SKU from the local storage values.
  const skuData = wishListItems.find((product) => product.sku === sku);

  // Return the given sku data if available else return null.
  return hasValue(skuData) ? skuData : null;
};

/**
 * Return the local storage array index for the given sku if available.
 *
 * @returns {number}
 *  Index of given sku in wishlist items array. Default to -1, if not found.
 */
export const getWishListDataIndexForSku = (sku) => {
  // Get existing wishlist data from storage.
  const wishListItems = getWishListData();

  // Return null if product sku is not in existing data.
  if (!wishListItems) {
    return null;
  }

  // Find the index of given sku in wishlist item array. This will return -1
  // if the given sku doesn't found in the array.
  return _.findIndex(wishListItems, (product) => product.sku === sku);
};

/**
 * Add wishlist information in the local storage.
 *
 * @param {object} wishListData
 *  An object of wishlist information.
 */
export const addWishListInfoInStorage = (wishListData, strgKey = null) => {
  // Configurable expiration time, by default it is 300s.
  const storageExpireTime = isAnonymousUser()
    ? getWishlistInfoStorageExpirationForGuest()
    : getWishlistInfoStorageExpirationForLoggedIn();

  // Check for the empty object and remove item from storage.
  if (typeof wishListData === 'object'
    && Object.keys(wishListData).length === 0) {
    Drupal.removeItemFromLocalStorage(
      hasValue(strgKey) ? strgKey : getWishListStorageKey(),
    );
    return;
  }

  // Store data to local storage.
  Drupal.addItemInLocalStorage(
    hasValue(strgKey) ? strgKey : getWishListStorageKey(),
    wishListData,
    storageExpireTime,
  );
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
  let productWithPsuedoAttribute = false;
  Object.keys(configurableAttributes).forEach((attributeCode) => {
    const attributeData = configurableAttributes[attributeCode];
    if (typeof attributeData.is_pseudo_attribute !== 'undefined'
      && attributeData.is_pseudo_attribute
      && typeof attributeData.values !== 'undefined'
      && attributeData.values.length > 0) {
      // Set a flag if the product has psuedo attribute from product data.
      // This flag is used to get required variant from combinations of options
      // ids without psuedo attribute value.
      productWithPsuedoAttribute = true;
    }
    // For the anonymous customers option's id key in product options object is
    // `option_id` but for authenticate customers this key is `id` only. So
    // while searching we need to put conditions with both the key and if either
    // one is matched, we need to process that attribute further.
    const attributeValueFromSku = skuData.options.find(
      (option) => ((option.option_id === attributeData.id
        || option.id === attributeData.id)
        ? option
        : false),
    );
    if (attributeValueFromSku) {
      // For authenticate customers we get the option value with the object key
      // `value' but for anonymous customers we have this in `option_value`.
      skuAttributesOptionData[attributeCode] = (typeof attributeValueFromSku.value !== 'undefined')
        ? attributeValueFromSku.value
        : attributeValueFromSku.option_value;
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

  // For products that have psuedo attribute we don't receive psuedo attribute
  // value in product options from MDC API for wishlist items. Hence in order to
  // select sku from remaining option ids. We use the combination without
  // psuedo attribute. The combination will select more than one child sku. eg:
  // band_size|183||cup_size|195||: "SX23874087",
  // band_size|183||cup_size|195||: "SX23875663"
  // We match the parent sku of above child sku with sku from wishlist
  // items to select the required variant.
  if (productWithPsuedoAttribute) {
    const variantsFromOptions = [];
    let selectedVariant = null;
    // Get the skus from product options combination.
    Object.entries(configurableCombinations.by_attribute).forEach((data) => {
      const [key, value] = data;
      if (key.indexOf(skuAttributeCombination) !== -1) {
        variantsFromOptions.push(value);
      }
    });

    if (variantsFromOptions.length > 0) {
      // Get all the variants from the product data.
      const { variants } = productData;
      variants.forEach((value) => {
        // From sku from combination and all variants we want the variant whose
        // parent is in the wishlist.
        // Check if parent sku match the sku from wishlist
        // and check the sku is present in variants from options.
        if (value.parent_sku === sku && variantsFromOptions.indexOf(value.sku) !== -1) {
          // Get variant sku whose parent is in wishlist
          // and sku matches product options.
          selectedVariant = variantsFromOptions.filter((item) => item === value.sku);
        }
      });
    }

    if (selectedVariant !== null) {
      return selectedVariant;
    }
  }

  // Return false if we don't find anything.
  return false;
};

/**
 * Utility function to add a product to wishlist for guest users.
 */
export const addProductToWishListForGuestUsers = (productInfo) => {
  // Get existing wishlist data from storage.
  let wishListItems = getWishListData();

  // If no existing data, create an array.
  wishListItems = wishListItems || [];

  // Add new data to storage.
  // We only need to store sku and options and not title.
  wishListItems.push({
    sku: productInfo.sku,
    options: productInfo.options,
  });

  // Save back to storage.
  addWishListInfoInStorage(wishListItems);

  // Always return a Promise object.
  return new Promise((resolve) => {
    resolve({ data: { status: true } });
  });
};

/**
 * Utility function to add a product to wishlist for logged in users.
 */
export const addProductToWishListForLoggedInUsers = (productInfo) => (
  window.commerceBackend.addRemoveWishlistItemsInBackend(
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
  window.commerceBackend.addRemoveWishlistItemsInBackend(
    productInfo,
    'removeWishlistItem',
  )
);

/**
 * Utility function to remove a product from wishlist.
 */
export const removeProductFromWishList = (productSku) => {
  // Get existing wishlist data from storage.
  const wishListItems = getWishListData();

  // Return is no existing data found.
  if (!wishListItems
    || !isProductExistInWishList(productSku)) {
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
  const skuData = getWishListDataForSku(productSku);
  if (skuData && typeof skuData.wishlistItemId === 'undefined') {
    // Always return a Promise object.
    return new Promise((resolve) => {
      resolve(null);
    });
  }

  // If wishlistItemId exists, do remove product from backend
  // and return the response of API call.
  return removeProductFromWishListForLoggedInUsers({
    wishlistItemId: skuData.wishlistItemId,
  });
};

/**
 * Utility function to prepare product details for wishlist.
 */
export const getWishlistLabel = () => (drupalSettings.wishlist.wishlist_label ? drupalSettings.wishlist.wishlist_label : '');

/**
 * Utility function to check if config for removing product from
 * wishlist after product added to cart is set to true.
 */
export const removeFromWishlistAfterAddtocart = () => {
  if (drupalSettings.wishlist && drupalSettings.wishlist.config
    && typeof drupalSettings.wishlist.config.removeAfterAddtocart !== 'undefined') {
    return drupalSettings.wishlist.config.removeAfterAddtocart;
  }
  return false;
};

/**
 * Utility function to get wishlist notification time.
 */
export const getWishlistNotificationTime = () => (3000);

/**
 * Helper function to check the stock status of the product with search
 * results and if user logged in then we will check in the local storage
 * wishlist data as well for backend stock status.
 */
export const getWishlistItemInStockStatus = (searchResult) => {
  // For logged in users we will check in wishlist local storage data.
  // If the 'is_in_stock' key is set for stock info, we will use that.
  if (!isAnonymousUser()) {
    const skuInfo = getWishListDataForSku(searchResult.sku);
    if (skuInfo !== null) {
      return (typeof skuInfo.inStock !== 'undefined'
        && skuInfo.inStock);
    }
  }

  // For anonymous user we check stock status in given search result record.
  if (typeof searchResult.stock !== 'undefined') {
    return (searchResult.stock !== 0);
  }

  // Return false by default.
  return true;
};

/**
 * Remove products from the wishlist which are not available in
 * given products array. We are using this function to show the
 * notification message on my wishlist page when products in
 * wishlist does not found in algolia search results.
 */
export const removeDiffFromWishlist = (productsObj) => {
  // Return if no products provided for diff check and remove.
  if (!hasValue(productsObj)) {
    return;
  }

  // Get existing wishlist data from storage.
  const wishListItems = getWishListData();

  if (wishListItems) {
    wishListItems.forEach((item) => {
      // Check if wishlist product sku exist in given
      // products array and if not, we will remove that
      // product from the wishlist of users.
      if (!productsObj.find(
        (productObj) => productObj.sku === item.sku,
      )) {
        // Call remove product from wishlist function. This
        // will handle removing product from wishlist for both
        // anonymous and logged in users.
        removeProductFromWishList(item.sku).then((response) => {
          if (hasValue(response)
            && hasValue(response.data)
            && hasValue(response.data.status)
            && response.data.status) {
            // Get the SKU index from the wishlist local storage array. This will
            // return a position index number i.e. > -1 if product exists else will
            // return -1. We need to remove product from the local storage if
            // skuIndex is greater than -1.
            const skuIndex = getWishListDataIndexForSku(item.sku);
            if (skuIndex !== null && skuIndex > -1) {
              // Remove the entry for given product sku from existing storage data.
              wishListItems.splice(skuIndex, 1);

              // Save back to storage.
              addWishListInfoInStorage(wishListItems);
            }
          }
        });
      }
    });
  }
};

/**
 *
 * @param {object} productData
 *  An object of product's data attributes.
 * @param {string} action
 *  Action context, can be add/remove (Default is add).
 */
export const pushWishlistSeoGtmData = (productData, action = 'add') => {
  if (productData.element === null) {
    logger.warning('Error in pushing data to GTM. productData: @productData.', {
      '@productData': JSON.stringify(productData),
    });
    return;
  }

  // Prepare and push product's variables to GTM using dataLayer.
  if (typeof Drupal.alshaya_seo_gtm_get_product_values !== 'undefined'
    && typeof Drupal.alshayaSeoGtmPushAddToWishlist !== 'undefined') {
    // Check the context, if cart page, prepare product data.
    if (typeof productData.context !== 'undefined'
      && typeof productData.variant !== 'undefined'
      && productData.context === 'cart') {
      // For the cart page get product info from storage.
      const key = `product:${drupalSettings.path.currentLanguage}:${productData.variant}`;
      const productInfo = Drupal.getItemFromLocalStorage(key);
      if (productInfo !== null
        && typeof Drupal.alshayaSeoSpc.gtmProduct !== 'undefined') {
        const product = Drupal.alshayaSeoSpc.gtmProduct(productInfo, 1);
        // For the cart page we only perform add to wishlist action.
        Drupal.alshayaSeoGtmPushAddToWishlist(product);
      }
      return;
    }

    // For the PLP, PDP and Modal contexts.
    // Get the seo GTM product values.
    let gtmProduct = productData.element.closest('[gtm-type="gtm-product-link"]');
    // The product drawer is coming in page end in DOM,
    // so element.closest is not right selector when quick view is open.
    if (gtmProduct === null) {
      const sku = productData.element.closest('form').getAttribute('data-sku') ? productData.element.closest('form').getAttribute('data-sku') : productData.variant;
      gtmProduct = document.querySelector(`article[data-sku="${sku}"]`);
    }
    const product = Drupal.alshaya_seo_gtm_get_product_values(
      gtmProduct,
    );

    // Set the product quantity.
    product.quantity = 1;

    // Create an object to store mapping of context and
    // GTM product view type value.
    const contextViewTypeMap = {
      productDrawer: 'quick_view',
      modal: 'recommendations_popup',
    };
    // Add product view type field for quick view and
    // recommendations popup/modal view.
    if (hasValue(productData.context)
      && hasValue(contextViewTypeMap[productData.context])) {
      product.product_view_type = contextViewTypeMap[productData.context];
    }

    // Set product variant to the selected variant.
    if (product.dimension2 !== 'simple' && (typeof productData.variant !== 'undefined' || typeof productData.sku !== 'undefined')) {
      product.variant = productData.variant || productData.sku;
    } else {
      product.variant = product.id;
    }

    // Check if the action and call the relevant GTM functions.
    switch (action) {
      case 'remove': {
        // Push removeFromWishlist event to datalayer.
        Drupal.alshayaSeoGtmPushRemoveFromWishlist(product);
        break;
      }

      default:
        // Push addToWishlist event to datalayer as detaul action.
        Drupal.alshayaSeoGtmPushAddToWishlist(product);
    }
  }
};
