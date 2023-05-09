import { hasValue } from './conditionsUtility';
import logger from './logger';

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
 * Gets data attribute for fixed price as object.
 *
 * @param {string} data
 *   Fixed prices object with country code and prices.
 * @param {string} field
 *   Price or special price to retrieve from data json.
 *
 * @returns {object}
 *   Country code and its fixed or special price.
 */
const getDataAttributePricesObj = (data, field) => {
  if (!hasValue(data) || !hasValue(field)) {
    return {};
  }

  if (typeof data !== 'string') {
    return {};
  }

  let fixedPriceAttributeData = {};

  try {
    // Get json object from string.
    fixedPriceAttributeData = JSON.parse(data);
  } catch (e) {
    // Return empty object if json parse has error.
    logger.error('Error while trying to parse the price data.', e);
    return {};
  }

  const prices = {};
  Object.entries(fixedPriceAttributeData).forEach(([key, value]) => {
    // Get prices for the given field for each currency from fixed_price field.
    prices[key] = value[field];
  });

  return prices;
};

/**
 * Gets data attribute for fixed price as json string.
 *
 * @param {string} data
 *   Fixed prices object with country code and prices.
 * @param {string} field
 *   Price or special price to retrieve from data json.
 *
 * @returns {string}
 *   Country code and its fixed or special price.
 */
const getDataAttributePrices = (data, field) => JSON.stringify(getDataAttributePricesObj(
  data,
  field,
));

const getFormattedPrice = (priceAmount) => {
  let amount = priceAmount === null ? 0 : priceAmount;

  // Remove commas if any.
  amount = amount.toString().replace(/,/g, '');
  amount = !Number.isNaN(Number(amount)) === true ? parseFloat(amount) : 0;

  return amount.toFixed(drupalSettings.alshaya_spc.currency_config.decimal_points);
};

export {
  calculateDiscount,
  getVatText,
  isFreeGiftProduct,
  getDataAttributePrices,
  getDataAttributePricesObj,
  getFormattedPrice,
};
