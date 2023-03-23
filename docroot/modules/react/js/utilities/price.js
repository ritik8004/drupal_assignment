import { hasValue } from './conditionsUtility';

/**
 * Calculates discount value from original and final price.
 *
 * @param {string} price
 *   The original price.
 * @param {string} finalPrice
 *   The final price after discount.
 *
 * @returns {Number}
 *   The discount value.
 */
const calculateDiscount = (price, finalPrice) => {
  const floatPrice = parseFloat(price);
  const floatFinalPrice = parseFloat(finalPrice);

  const discount = floatPrice - floatFinalPrice;
  if (floatPrice < 0.1 || floatFinalPrice < 0.1 || discount < 0.1) {
    return 0;
  }

  return parseFloat(Math.round((discount * 100) / floatPrice));
};

/**
 * Returns the vat text.
 *
 * @returns string
 *   The vat text.
 */
const getVatText = () => (
  drupalSettings.vat_text !== null
    ? drupalSettings.vat_text
    : ''
);

/**
 * Check If product is free or not using its price.
 *
 * @returns {boolean}
 *   True if product is free.
 */
const isFreeGiftProduct = (price) => {
  if (price === 0 || price === 0.01) {
    return true;
  }
  return false;
};

/**
 * Gets data attribute for fixed price.
 *
 * @param {string} data
 *   Fixed prices object with country code and prices.
 * @param {string} field
 *   Price or special price to retrieve from data json.
 *
 * @returns {string}
 *   Country code and its fixed or special price.
 */
const getDataAttributePrices = (data, field) => {
  if (!hasValue(data) || !hasValue(field)) {
    return '';
  }

  if (typeof data !== 'string') {
    return '';
  }

  let fixedPriceAttributeData = {};

  try {
    // Get json object from string.
    fixedPriceAttributeData = JSON.parse(data);
  } catch (e) {
    // Return empty string if json parse has error.
    return '';
  }

  const prices = {};
  Object.entries(fixedPriceAttributeData).forEach(([key, value]) => {
    // Get prices for the given field for each currency from fixed_price field.
    prices[key] = value[field];
  });

  return JSON.stringify(prices);
};

export {
  calculateDiscount,
  getVatText,
  isFreeGiftProduct,
  getDataAttributePrices,
};
