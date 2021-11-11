import { isMaxSaleQtyEnabled } from '../../../js/utilities/display';

/**
 * Handle the response once the cart is updated.
 */
export const handleUpdateCartRespose = (response, productData, postData) => {
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

    // Show minicart notification.
    const form = document.getElementsByClassName('sku-base-form')[0];
    const cartNotificationEvent = new CustomEvent('product-add-to-cart-success', {
      bubbles: true,
      detail: {
        productData: productInfo,
        cartData: response.data,
        postData,
        noGtm: true,
      },
    });
    form.dispatchEvent(cartNotificationEvent);
  }

  return response;
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
  return window.commerceBackend.addUpdateRemoveCartItem(postData).then(
    (response) => {
      // Prepare product data.
      const productData = {
        quantity: requestData.qty,
        sku: requestData.sku,
        parentSku: requestData.sku,
        options: requestData.optionsForGtm,
        variant: requestData.variant,
        image: requestData.productImage,
        product_name: requestData.productCartTitle,
      };

      if (response.data.error) {
        // Prepare and dispatch the add to cart failed event.
        const form = document.getElementsByClassName('sku-base-form')[0];
        const productAddToCartFailed = new CustomEvent('product-add-to-cart-failed', {
          bubbles: true,
          detail: {
            postData,
            productData,
            message: response.error_message,
          },
        });
        form.dispatchEvent(productAddToCartFailed);
      }

      return handleUpdateCartRespose(
        response,
        productData,
        postData,
      );
    },
  );
};

/**
 *
 * @param {object} productData
 *  An object of product's data attributes.
 */
export const pushSeoGtmData = (productData) => {
  // Prepare and push product's variables to GTM using dataLayer.
  if (typeof Drupal.alshaya_seo_gtm_get_product_values !== 'undefined'
    && typeof Drupal.alshayaSeoGtmPushAddToCart !== 'undefined') {
    // Get the seo GTM product values.
    let gtmProduct = productData.element.closest('[gtm-type="gtm-product-link"]');
    // The product drawer is coming in page end in DOM,
    // so element.closest is not right selector when quick view is open.
    if (gtmProduct === null) {
      gtmProduct = document.querySelector(`article[data-sku="${productData.sku}"]`);
    }
    const product = Drupal.alshaya_seo_gtm_get_product_values(
      gtmProduct,
    );

    // Set the product quantity.
    product.quantity = Math.abs(productData.qty);

    // Set product variant to the selected variant.
    if (product.dimension2 !== 'simple' && typeof productData.variant !== 'undefined') {
      product.variant = productData.variant;
    } else {
      product.variant = product.id;
    }

    Drupal.alshayaSeoGtmPushAddToCart(product);
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
      option_id: configurableAttributes[attributeName].attribute_id,
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
      const attributeId = configurableAttributes[attributeNames[i]].attribute_id;
      const optionId = option.option_id;
      if (attributeId.toString() === optionId.toString()) {
        for (let j = 0; j < configurableAttributes[attributeNames[i]].values.length; j++) {
          if (configurableAttributes[attributeNames[i]].values[j].value_id
            === option.option_value) {
            return `${configurableAttributes[attributeNames[i]].label}: ${configurableAttributes[attributeNames[i]].values[j].label}`;
          }
        }
      }
    }
    return true;
  });

  return optionsForGtm;
};

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
  if (selectedVariant !== null) {
    maxSaleQtyOfSelectedVariant = parseInt(productData.variants[selectedVariant].maxSaleQty, 10);
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
    Object.entries(productData.variants).forEach((variant) => {
      const childSku = variant.sku;
      if (typeof cartData.items[childSku] !== 'undefined') {
        cartQtyForVariant += cartData.items[childSku].qty;
      }
    });

    // Check if max sale quantity limit has been reached.
    if (cartQtyForVariant >= maxSaleQtyOfSelectedVariant
      && maxSaleQtyOfSelectedVariant > 0) {
      return true;
    }
  }

  return false;
};
