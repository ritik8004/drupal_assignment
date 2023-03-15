import { hasValue } from '../../../../js/utilities/conditionsUtility';

const formatPrice = (price) => {
  const priceParts = [
    drupalSettings.reactTeaserView.price.currency.toUpperCase(),
    price.toFixed(drupalSettings.reactTeaserView.price.decimalPoints),
  ];

  return drupalSettings.reactTeaserView.price.currencyPosition === 'before'
    ? priceParts.join(' ')
    : priceParts.reverse().join(' ');
};

const getPriceRangeLabel = (value) => {
  if (value === '') {
    return (null);
  }
  const [startStr, endStr] = value.split(':');

  const startPrice = (startStr !== '') ? parseFloat(startStr) : 0;
  const endPrice = parseFloat(endStr);

  const label = (startStr === '')
    ? Drupal.t('under @stop', { '@stop': formatPrice(endPrice) })
    : Drupal.t('@start - @stop', {
      '@start': formatPrice(startPrice),
      '@stop': formatPrice(endPrice),
    });

  return label;
};

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
  formatPrice,
  calculateDiscount,
  getPriceRangeLabel,
  getDataAttributePrices,
};
