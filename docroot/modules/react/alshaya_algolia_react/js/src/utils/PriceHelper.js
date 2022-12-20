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
 * @param {object} data
 *   Fixed prices object with country code and prices.
 * @param {string} field
 *   Price or special price to retrieve from data json.
 *
 * @returns {object|string}
 *   Country code and its fixed or special price.
 */
const getDataAttributePrices = (data, field) => {
  if (hasValue(data)) {
    return '';
  }
  const prices = {};
  Object.entries(data).forEach(([key, value]) => {
    prices[key] = value[field];
  });

  return prices;
};

export {
  formatPrice,
  calculateDiscount,
  getPriceRangeLabel,
  getDataAttributePrices,
};
