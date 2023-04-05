import isCartNotificationDrawerEnabled from '../../../js/utilities/cartNotificationHelper';
import { hasValue } from '../../../js/utilities/conditionsUtility';
import dispatchCustomEvent from '../../../js/utilities/events';
import logger from '../../../js/utilities/logger';

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
      // If cart drawer feature is enabled, we show side drawer for cart.
      // Else we show mini cart notification.
      if (isCartNotificationDrawerEnabled()) {
        dispatchCustomEvent('showCartNotificationDrawer', { productInfo });
      } else if (hasValue(productInfo.skuType) && productInfo.skuType === 'config') {
        // To show notification for config products once drawer is closed.
        setTimeout(() => {
          Drupal.cartNotification.triggerNotification(productInfo);
        }, 600);
      } else {
        Drupal.cartNotification.triggerNotification(productInfo);
      }
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
    variant_sku: (typeof requestData.variant !== 'undefined') ? requestData.variant : requestData.sku,
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
        skuType: requestData.skuType,
      };

      return handleUpdateCartRespose(
        response,
        productData,
      );
    },
  );
};

/**
 * Helper function to check if GTM Product push condition is enabled.
 */
export const isGtmProductPushEnabled = () => {
  if (Drupal.hasValue(drupalSettings.add_to_bag)) {
    return drupalSettings.add_to_bag.gtm_product_push;
  }

  return false;
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
export const getHiddenFormAttributes = () => (typeof drupalSettings.hidden_form_attributes !== 'undefined'
  ? drupalSettings.hidden_form_attributes
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
  attributesAndValues,
  selectedFormValues,
) => {
  // Create clones to allow modification.
  let attributesHierarchyClone = JSON.parse(JSON.stringify(attributesHierarchy));
  let attributesAndValuesClone = { ...attributesAndValues };

  const selectedFormAttributeNames = Object.keys(selectedFormValues);

  for (let i = 0; i < selectedFormAttributeNames.length; i++) {
    const code = selectedFormAttributeNames[i];

    if (code === attributeName) {
      break;
    }

    // Update the next level selection if current selection is no longer valid.
    // Example:
    // color: blue, band_size: 30, cup_size: c
    // color: red, band_size: 32, cup_size: d
    // For above, when user changes from 30 to 32 for band_size,
    // cup_size still says selected as c. But it's not available
    // for 32 and hence we need to update the selected combination.
    if (typeof attributesHierarchyClone[code][selectedFormValues[code]] === 'undefined') {
      // Not sure why javascript hates pass by reference.
      // We need it here on purpose so disabling linting.
      // eslint-disable-next-line no-param-reassign
      [selectedFormValues[code]] = Object.keys(attributesHierarchyClone[code]);
    }

    // Shorten the attribute hierarchy in order to move to the attributeName
    // which has been updated.
    attributesHierarchyClone = attributesHierarchyClone[code][selectedFormValues[code]];
  }

  // Add all the configurable options for current attribute.
  // For next level we would add only for the current selected
  // option. So if we have following and we select blue,
  // we want both m and l. This is more appropriate when we have
  // 2+ configurable attributes.
  // {
  //   red: s, m
  //   blue: m, l
  //   green: s, m
  // }
  attributesAndValuesClone[attributeName] = Object.keys(attributesHierarchyClone[attributeName]);

  // Check if the end of the hierarchy has been reached.
  const nextLevel = Object.values(attributesHierarchyClone[attributeName])[0];
  if (typeof nextLevel !== 'object') {
    return attributesAndValuesClone;
  }

  // Get the next attribute in the hierarchy.
  const nextAttribute = Object.keys(nextLevel)[0];

  if (typeof nextAttribute === 'undefined') {
    return attributesAndValuesClone;
  }

  // Do a recursive call to travel further down the attribute hierarchy.
  attributesAndValuesClone = getAllowedAttributeValues(
    attributesHierarchy,
    nextAttribute,
    attributesAndValuesClone,
    selectedFormValues,
  );

  return attributesAndValuesClone;
};

/**
 * Gets the default values of the attributes which are enabled for display.
 * @see Drupal.refreshConfigurables().
 *
 * @param {object} activeSwatchData
 *   The hierarchy of attributes under the main attribute.
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
 * @param {string} attribute
 *   The name of the selected attribute.
 * @param {object} selectedFormValues
 *   The array of selected form values like {attr1: val1, attr2: val2....}.
 * @param {object} configurableAttributes
 *   The configurable attributes data.
 *
 * @returns {string}
 *   The default attribute value.
 */
export const getDefaultAttributeValues = (
  activeSwatchData,
  attribute,
  selectedFormValues,
  configurableAttributes,
) => {
  const selectedFormAttributeNames = Object.keys(selectedFormValues);
  let defaultAttributeValue = '';

  for (let i = 0; i < selectedFormAttributeNames.length; i++) {
    const code = selectedFormAttributeNames[i];

    if (typeof activeSwatchData !== 'undefined' && typeof activeSwatchData[code] !== 'undefined') {
      if (code === attribute) {
        // Get all other available attributes for a given attribute.
        const availableValues = Object.keys(activeSwatchData[code]);
        let defaultAttr = '';

        // Check if the fist available attribute is valid.
        Object.values(configurableAttributes[code].values).some((attributeValues) => {
          if (availableValues.includes(attributeValues.value)) {
            defaultAttr = attributeValues.value;
            return true;
          }
          return false;
        });
        return defaultAttr;
      }

      // Get the next attribute data from the hierarchy and check if the end of
      // the hierarchy has been reached.
      const nextLevelData = activeSwatchData[code][selectedFormValues[code]];
      if (typeof nextLevelData !== 'object') {
        return defaultAttributeValue;
      }

      // Create clones to allow modification.
      const selectedFormValuesClone = JSON.parse(JSON.stringify(selectedFormValues));
      delete selectedFormValuesClone[code];

      // Do a recursive call to travel further down the attribute hierarchy.
      defaultAttributeValue = getDefaultAttributeValues(
        nextLevelData,
        attribute,
        selectedFormValuesClone,
        configurableAttributes,
      );
    }
  }

  return defaultAttributeValue;
};

/**
 * Returns the allowed values for quantity for the quantity dropdown.
 *
 * @param {string} selectedVariant
 *   The selected variant string.
 * @param {object} productData
 *   Full product information object.
 *
 * @returns array
 *   The list of allowed values for quantity.
 */
export const getQuantityDropdownValues = (selectedVariant, productData) => {
  const qty = drupalSettings.add_to_bag.show_quantity
    && typeof drupalSettings.add_to_bag.cart_quantity_options === 'object'
    ? Object.values(drupalSettings.add_to_bag.cart_quantity_options)
    : [];

  // Get the selected variant's information.
  let variantInfo = null;
  for (let index = 0; index < productData.variants.length; index++) {
    if (productData.variants[index].sku === selectedVariant) {
      variantInfo = productData.variants[index];
      break;
    }
  }

  // Return is variant information is not available.
  if (variantInfo === null) {
    return qty;
  }

  // Remove quantity option if option is greater than stock quantity.
  const options = [];
  qty.forEach((val) => {
    if (variantInfo.stock.status
      && val <= variantInfo.stock.qty) {
      options.push(val);
    }
  });
  return options;
};

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

  if (productData.element === null) {
    logger.warning('Error in pushing data to GTM. productData: @productData.', {
      '@productData': JSON.stringify(productData),
    });
    return;
  }

  // Get the quantity difference.
  const diffQty = productData.qty - productData.prevQty;

  // Prepare and push product's variables to GTM using dataLayer.
  if (typeof Drupal.alshaya_seo_gtm_get_product_values !== 'undefined'
    && typeof Drupal.alshayaSeoGtmPushAddToCart !== 'undefined') {
    // Get the seo GTM product values.
    let gtmProduct = productData.element.closest('[gtm-type="gtm-product-link"]');
    // The product drawer is coming in page end in DOM,
    // so element.closest is not right selector when quick view is open.
    if (gtmProduct === null) {
      gtmProduct = document.querySelector(`article[data-sku="${productData.element.getAttribute('data-sku')}"]`);
    }
    const product = Drupal.alshaya_seo_gtm_get_product_values(
      gtmProduct,
    );

    // Set the product quantity.
    product.quantity = Math.abs(diffQty);

    // Set product variant to the selected variant.
    if (product.dimension2 !== 'simple' && typeof productData.variant !== 'undefined') {
      product.variant = productData.variant;
    } else {
      product.variant = product.id;
    }
    // Add new data layer variable in Add To Cart,
    // Event on Quick View.
    if (Drupal.hasValue(productData.product_view_type)) {
      product.product_view_type = 'quick_view';
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
 *  Time in seconds format.
 */
export const getProductinfoLocalStorageExpiration = () => (typeof drupalSettings.add_to_bag.productinfo_local_storage_expiration !== 'undefined'
  ? (parseInt(drupalSettings.add_to_bag.productinfo_local_storage_expiration, 10) * 60)
  : 0);

/**
 * Get the product info local storage key.
 * Encoded sku so the sku with slash(s) doesn't break the key.
 *
 * @returns {string}
 */
export const getProductInfoStorageKey = (sku) => (`productinfo:${btoa(sku)}:${drupalSettings.path.currentLanguage}`);

/**
 * Add product's information in the local storage for given sku.
 *
 * @param {object} productData
 *  An object of product's information.
 * @param {string} sku
 *  Sku of product.
 */
export const addProductInfoInStorage = (productData, sku) => {
  // Get local storage key for the product.
  const storageKey = getProductInfoStorageKey(sku);

  // Store data to local storage.
  Drupal.addItemInLocalStorage(
    storageKey,
    productData,
    getProductinfoLocalStorageExpiration(),
  );
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
