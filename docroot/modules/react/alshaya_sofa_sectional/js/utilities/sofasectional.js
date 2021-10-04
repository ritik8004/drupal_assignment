import { isMaxSaleQtyEnabled } from '../../../js/utilities/display';

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
