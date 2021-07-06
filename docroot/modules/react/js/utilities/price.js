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

export { calculateDiscount, getVatText };
