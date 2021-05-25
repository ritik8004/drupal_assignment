import axios from 'axios';
import { getStorageInfo, removeStorageInfo, setStorageInfo } from '../../../alshaya_spc/js/utilities/storage';

/**
 * Ajax call for updating the cart.
 */
export const updateCart = (postData) => window.commerceBackend.addUpdateRemoveCartItem(postData);

/**
 * Handle the response once the cart is updated.
 */
export const handleUpdateCartRespose = (response, productData) => {
  const productInfo = productData;

  // Return response data if the error present to process it by component itself.
  if (response.data.error === true) {
    return response.data;
  }

  if (response.data.cart_id) {
    if ((typeof response.data.items[productInfo.sku] !== 'undefined')) {
      const cartItem = response.data.items[productInfo.sku];
      productInfo.totalQty = cartItem.qty;
    }

    // Dispatch event to refresh the react minicart component.
    const refreshMiniCartEvent = new CustomEvent('refreshMiniCart', { bubbles: true, detail: { data() { return response.data; }, productInfo } });
    document.dispatchEvent(refreshMiniCartEvent);

    // Dispatch event to refresh the cart data.
    const refreshCartEvent = new CustomEvent('refreshCart', { bubbles: true, detail: { data() { return response.data; } } });
    document.dispatchEvent(refreshCartEvent);

    // Show minicart notification if product exist in cart
    // and notification is enabled.
    if ((typeof productInfo.notify !== 'undefined') && productInfo.notify !== false) {
      // To disable scroll after minicart notification display.
      productInfo.noScroll = true;
      Drupal.cartNotification.triggerNotification(productInfo);
    }
  }

  return response;
};

/**
 * Triggers minicart notification with text.
 *
 * @param {string} message
 *   The text message for the notification.
 */
export const triggerCartTextNotification = (message, type) => {
  Drupal.cartNotification.triggerNotification({
    isTextNotification: true,
    text: message,
    noScroll: true,
    type,
  });
};

/**
 * Add or update products to the cart.
 */
export const triggerUpdateCart = (requestData) => {
  // Prepare post data.
  const postData = {
    action: requestData.action,
    sku: requestData.sku,
    quantity: requestData.qty,
    cart_id: requestData.cartId,
    options: requestData.options,
    variant: (typeof requestData.variant !== 'undefined') ? requestData.variant : requestData.sku,
  };

  // Call update cart api function.
  return updateCart(postData).then(
    (response) => {
      // Prepare product data.
      const productData = {
        quantity: requestData.qty,
        sku: requestData.sku,
        variant: requestData.variant,
        image: requestData.productImage,
        product_name: requestData.productCartTitle,
        notify: requestData.notify,
      };

      return handleUpdateCartRespose(
        response,
        productData,
      );
    },
  );
};

/**
 * Helper function to check if max sale quantity condition is enabled.
 */
export const isMaxSaleQtyEnabled = () => {
  if (typeof drupalSettings.add_to_bag !== 'undefined') {
    return drupalSettings.add_to_bag.max_sale_quantity_enabled;
  }

  return false;
};

/**
 * Helper function to check if max sale quantity message is enabled.
 */
export const isHideMaxSaleMsg = () => {
  if (typeof drupalSettings.add_to_bag !== 'undefined') {
    return drupalSettings.add_to_bag.max_sale_hide_message;
  }

  return false;
};

/**
 * Returns the array of hidden form attribute names.
 *
 * @returns array
 *  The array of hidden form attribute names.
 */
export const getHiddenFormAttributes = () => (typeof drupalSettings.add_to_bag.hidden_form_attributes !== 'undefined'
  ? drupalSettings.add_to_bag.hidden_form_attributes
  : []);

/**
 * Gets the attributes which are enabled for display.
 * @see Drupal.refreshConfigurables().
 *
 * @param {object} attributesHierarchy
 *   The hierarchy of attributes.
 *    "article_castor_id": {
 *      "8010": {
 *       "size": {
 *         "7045": {
 *          "season_code": {
 *            "39937": "1"
 *          }
 *         }
 *       }
 *      }
 *     }
 *
 * @param {string} attributeName
 *   The name of the selected attribute.
 * @param {string} attributeValue
 *   The value of the selected attribute.
 * @param {array} attributesAndValues
 *   An array keyed by attribute names with the array of allowed attribute
 * values based on the value of the initial attribute. This will be returned.
 * @param {object} selectedFormValues
 *   The array of selected form values like {attr1: val1, attr2: val2....}.
 *
 * @returns {array}
 *   Array keyed by attribute names with the array of allowed attribute
 * values.
 */
export const getAllowedAttributeValues = (
  attributesHierarchy,
  attributeName,
  attributeValue,
  attributesAndValues,
  selectedFormValues,
) => {
  // Create clones to allow modification.
  let attributesHierarchyClone = JSON.parse(JSON.stringify(attributesHierarchy));
  const selectedFormValuesClone = { ...selectedFormValues };
  let attributesAndValuesClone = { ...attributesAndValues };

  const selectedFormAttributeNames = Object.keys(selectedFormValuesClone);
  for (let i = 0; i < selectedFormAttributeNames.length; i++) {
    const code = selectedFormAttributeNames[i];

    if (code === attributeName) {
      break;
    }
    // Shorten the attribute hierarchy in order to move to the attributeName
    // which has been updated.
    attributesHierarchyClone = attributesHierarchyClone[code][selectedFormValuesClone[code]];
  }

  // Check if the end of the hierarchy has been reached.
  /* eslint-disable eqeqeq */
  if (attributesHierarchyClone[attributeName] == 1) {
    return attributesAndValuesClone;
  }

  // If the key for the attribute does not exist, create one.
  if (typeof attributesAndValuesClone[attributeName] === 'undefined') {
    attributesAndValuesClone[attributeName] = [];
  }

  // Push the selected attribute into the array.
  attributesAndValuesClone[attributeName].push(attributeValue);

  // Get the next attribute in the hierarchy.
  const nextAttribute = Object.keys(
    attributesHierarchyClone[attributeName][attributeValue],
  )[0];

  if (typeof nextAttribute === 'undefined') {
    return attributesAndValuesClone;
  }

  // Get the list of values for the next attribute in the hierarchy.
  const nextValues = Object.keys(
    attributesHierarchyClone[attributeName][attributeValue][nextAttribute],
  );

  nextValues.forEach((nextAttributeVal) => {
    selectedFormValuesClone[nextAttribute] = nextAttributeVal;
    // Do a recursive call to travel further down the attribute hierarchy.
    attributesAndValuesClone = getAllowedAttributeValues(
      attributesHierarchy,
      nextAttribute,
      nextAttributeVal,
      attributesAndValuesClone,
      selectedFormValuesClone,
    );
  });

  return attributesAndValuesClone;
};

/**
 * Gets the first value among the array of values for each attribute.
 *
 * @param {object} attributesAndValues
 *   An object of attribute keys and their value like
 * {attr1: [val1, val2...], attr2: [val1, val2...]}.
 *
 * @returns {object}
 *   Object containing attribute keys and the first values like
 *  {attr1: val1, attr2: val2,....}.
 */
export const getFirstAttributesAndValues = (attributesAndValues) => {
  const data = [];
  Object.keys(attributesAndValues).forEach((attribute) => {
    [data[attribute]] = attributesAndValues[attribute];
  });

  return data;
};

/**
 * Returns the allowed values for quantity for the quantity dropdown.
 *
 * @returns array
 *   The list of allowed values for quantity.
 */
export const getQuantityDropdownValues = () => (
  drupalSettings.add_to_bag.show_quantity
  && typeof drupalSettings.add_to_bag.cart_quantity_options === 'object'
    ? Object.values(drupalSettings.add_to_bag.cart_quantity_options)
    : []
);

/**
 *
 * @param {object} productData
 *  An object of product's data attributes.
 */
export const pushSeoGtmData = (productData) => {
  // Prepare and push product's variables to GTM using dataLayer.
  if (productData.error && typeof Drupal.alshayaSeoGtmPushAddToCartFailure !== 'undefined') {
    const message = typeof productData.options !== 'undefined'
      ? `Update cart failed for Product [${productData.sku}] ${productData.options}`
      : `Update cart failed for Product [${productData.sku}] `;
    Drupal.alshayaSeoGtmPushAddToCartFailure(
      message,
      productData.error_message,
    );

    return;
  }

  // Get the quantity difference.
  const diffQty = productData.qty - productData.prevQty;

  // Prepare and push product's variables to GTM using dataLayer.
  if (typeof Drupal.alshaya_seo_gtm_get_product_values !== 'undefined'
    && typeof Drupal.alshayaSeoGtmPushAddToCart !== 'undefined') {
    // Get the seo GTM product values.
    const product = Drupal.alshaya_seo_gtm_get_product_values(
      productData.element.closest('[gtm-type="gtm-product-link"]'),
    );

    // Set the product quantity.
    product.quantity = Math.abs(diffQty);

    // Set product variant to the selected variant.
    if (product.dimension2 !== 'simple' && typeof productData.variant !== 'undefined') {
      product.variant = productData.variant;
    } else {
      product.variant = product.id;
    }

    if (diffQty < 0) {
      // Trigger removeFromCart.
      Drupal.alshayaSeoGtmPushRemoveFromCart(product);
    } else {
      // Trigger addToCart.
      Drupal.alshayaSeoGtmPushAddToCart(product);
    }
  }
};

/**
 * Get the array of selected options for cart operation.
 *
 * @param {object} configurableAttributes
 *   The cofigurable attributes data.
 * @param {object} formAttributeValues
 *   The selected attribute names and values.
 * @param {boolean} retainPseudoAttribute
 *   Whether to let the pseudo attribute remain in the returned array.
 *
 * @returns array
 *   The array of selected options.
 */
export const getSelectedOptionsForCart = (
  configurableAttributes,
  formAttributeValues,
  retainPseudoAttribute,
) => {
  const options = [];
  // Prepare the array of selected options.
  Object.keys(configurableAttributes).forEach((attributeName) => {
    if (!retainPseudoAttribute && configurableAttributes[attributeName].is_pseudo_attribute) {
      return;
    }
    const option = {
      option_id: configurableAttributes[attributeName].id,
      option_value: formAttributeValues[attributeName],
    };
    options.push(option);
  });

  return options;
};

/**
 * Get the array of selected options for sending to GTM.
 *
 * @param {object} configurableAttributes
 *   The cofigurable attributes data.
 * @param {object} formAttributeValues
 *   The selected attribute names and values.
 *
 * @returns array
 *   The array of selected options for GTM.
 */
export const getSelectedOptionsForGtm = (
  configurableAttributes,
  formAttributeValues,
) => {
  let optionsForGtm = getSelectedOptionsForCart(configurableAttributes, formAttributeValues, true);

  optionsForGtm = optionsForGtm.map((option) => {
    const attributeNames = Object.keys(configurableAttributes);
    for (let i = 0; i < attributeNames.length; i++) {
      if (configurableAttributes[attributeNames[i]].id === option.option_id) {
        for (let j = 0; j < configurableAttributes[attributeNames[i]].values.length; j++) {
          if (configurableAttributes[attributeNames[i]].values[j].value === option.option_value) {
            return `${configurableAttributes[attributeNames[i]].label}: ${configurableAttributes[attributeNames[i]].values[j].label}`;
          }
        }
      }
    }
    return true;
  });

  return optionsForGtm.join(', ');
};

/**
 * Returns the product info local storage expiration time.
 *
 * @returns integer
 *  Time in minutes format.
 */
export const getProductinfoLocalStorageExpiration = () => (typeof drupalSettings.add_to_bag.productinfo_local_storage_expiration !== 'undefined'
  ? parseInt(drupalSettings.add_to_bag.productinfo_local_storage_expiration, 10)
  : 0);

/**
 * Get the product info local storage key.
 * Encoded sku so the sku with slash(s) doesn't break the key.
 *
 * @returns {string}
 */
export const getProductInfoStorageKey = (sku) => (`productinfo:${btoa(sku)}:${drupalSettings.path.currentLanguage}`);

/**
 * Remove product information from the local storage.
 *
 * @param {string} sku
 */
export const removeProductInfoInStorage = (sku) => {
  // Get local storage key for the product.
  const storageKey = getProductInfoStorageKey(sku);
  removeStorageInfo(storageKey);
};

/**
 * Add product's information in the local storage for given sku.
 *
 * @param {object} productData
 *  An object of product's information.
 * @param {string} sku
 *  Sku of product.
 */
export const addProductInfoInStorage = (productData, sku) => {
  const productInfo = { ...productData };

  // Adding current time to storage to know the last time data updated.
  productInfo.last_update = new Date().getTime();

  // Get local storage key for the product.
  const storageKey = getProductInfoStorageKey(sku);

  // Store data to local storage.
  setStorageInfo(productInfo, storageKey);
};

/**
 * Return the product's info available in local storage.
 * Return null if product's info in local storage is expired.
 *
 * @param {string} sku
 *  Sku of the product.
 *
 * @returns {object}
 *  An object of product's information.
 */
export const productInfoAvailableInStorage = (sku) => {
  // Get local storage key for the product.
  const storageKey = getProductInfoStorageKey(sku);

  // Get data from local storage.
  const productInfo = getStorageInfo(storageKey);

  // If data is not available in storage, we flag it to check/fetch from api.
  if (!productInfo || !productInfo.infoData || !productInfo.infoData.title) {
    return null;
  }

  // Configurable expiration time, by default it is 10m.
  const storageExpireTime = getProductinfoLocalStorageExpiration();
  const expireTime = storageExpireTime * 60 * 1000;
  const currentTime = new Date().getTime();

  // If data is expired, we flag it to check/fetch from api.
  if ((currentTime - productInfo.last_update) > expireTime) {
    return null;
  }

  return productInfo.infoData;
};

/**
 * Get the product information if available in local storage. If not,
 * fetch the information from the API and return a promise object.
 *
 * @param {string} sku
 *  Sku of the product to get information for.
 *
 * @returns {object}
 *  An object of the product's information.
 */
export const getProductInfo = (sku) => {
  // Return null if the sku is undefined or null.
  if (typeof sku === 'undefined' || sku === null) {
    removeProductInfoInStorage(sku);
    return new Promise((resolve) => {
      resolve(null);
    });
  }

  // Check and return if product info available in storage and not expired.
  const productInfo = productInfoAvailableInStorage(sku);

  // Return product info if available in storage.
  if (productInfo !== null) {
    // Return a promise object always.
    return new Promise((resolve) => {
      resolve(productInfo);
    });
  }

  // If product's info isn't available, fetch via api.
  // Prepare the product info api url.
  const apiUrl = Drupal.url(`rest/v1/product-info/${btoa(sku)}`);

  return axios.get(apiUrl).then((response) => {
    if (typeof response !== 'object') {
      removeProductInfoInStorage(sku);
      return new Promise((resolve) => {
        resolve(null);
      });
    }

    return response.data;
  }).catch((error) => {
    // Processing of error here.
    Drupal.logJavascriptError('Failed to fetch product data.', error, 'product_info_resource');
    return error;
  });
};

export const isMaxSaleQtyReached = (selectedVariant, productData) => {
  // Early return if max sale quantity check is disabled.
  if (!isMaxSaleQtyEnabled()) {
    return false;
  }

  // Fetch the cart data present in local storage.
  const cartData = Drupal.alshayaSpc.getCartData();

  // Return if cart is empty.
  if (!cartData) {
    return false;
  }

  // Define the default cart and max sale quantity 0.
  let cartQtyForVariant = 0;
  let maxSaleQtyOfSelectedVariant = 0;

  // We will check for the sku specific max sale limit first.
  // Get the product quantity from cart items for the selected variant only.
  if (typeof cartData.items[selectedVariant] !== 'undefined') {
    cartQtyForVariant = cartData.items[selectedVariant].qty;
  }

  // Get the product max sale quantity for the selected variant only.
  for (let index = 0; index < productData.variants.length; index++) {
    if (productData.variants[index].sku === selectedVariant) {
      maxSaleQtyOfSelectedVariant = productData.variants[index].max_sale_qty;
      break;
    }
  }

  // Check if max sale quantity limit has been reached.
  if (cartQtyForVariant >= maxSaleQtyOfSelectedVariant
    && maxSaleQtyOfSelectedVariant > 0) {
    return true;
  }

  // If the sku specific max sale limit is not reached,
  // If the max sale quantity is enable for the parent sku,
  // then we will sum all the child sku's quantity in cart.
  if (productData.max_sale_qty_parent_enable) {
    // Get the max sale quantity limit from the parent.
    maxSaleQtyOfSelectedVariant = productData.max_sale_qty_parent;

    // If max sale for parent sku enable, we will sum all the child variants
    // quantity in cart and consider the global qty limit.
    for (let index = 0; index < productData.variants.length; index++) {
      const childSku = productData.variants[index].sku;
      if (typeof cartData.items[childSku] !== 'undefined') {
        cartQtyForVariant += cartData.items[childSku].qty;
      }
    }

    // Check if max sale quantity limit has been reached.
    if (cartQtyForVariant >= maxSaleQtyOfSelectedVariant
      && maxSaleQtyOfSelectedVariant > 0) {
      return true;
    }
  }

  return false;
};
