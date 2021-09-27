/**
 * Fetches data from the remote source and returns to the callback.
 *
 * @param {string} url
 *   The of the remote source.
 * @param {string} method
 *   The request method.
 * @param {object} options
 *   The request options like data, headers etc.
 * @param {string} callback
 *   The function to call on successful response.
 */
exports.invokeApi = async function (request) {
  const headers = {};

  if (typeof request.headers !== 'undefined') {
    request.headers.forEach(function (header) {
      headers[header[0]] = header[1];
    });
  }

  return jQuery.ajax({
    url: drupalSettings.alshayaRcs.commerceBackend.baseUrl + '/' + request.uri,
    method: request.method,
    headers,
    data: request.data,
    success: function (response) {
      return response;
    },
    error: function () {
      console.log('Could not fetch data!');
    }
  });
};

/**
 * Fetches data from remote source and returns to the callback synchronously.
 *
 * @param {string} url
 *   The of the remote source.
 * @param {string} method
 *   The request method.
 * @param {object} options
 *   The request options like data, headers etc.
 * @param {string} callback
 *   The function to call on successful response.
 */
 exports.invokeApiAsync = function (request) {
  const headers = {};
  let result = null;

  if (typeof request.headers !== 'undefined') {
    request.headers.forEach(function (header) {
      headers[header[0]] = header[1];
    });
  }

  jQuery.ajax({
    url: drupalSettings.alshayaRcs.commerceBackend.baseUrl + '/' + request.uri,
    method: request.method,
    headers,
    async: false,
    data: request.data,
    success: function (response) {
      result = response;
    },
    error: function () {
      console.log('Could not fetch data!');
    }
  });

  return result;
};

/**
 * Get the amount with the proper format for decimals.
 *
 * @param priceAmount
 *   The price amount.
 *
 * @returns {string|*}
 *   Return string with price and currency or return array of price and
 *   currency.
 */
exports.getFormattedAmount = function (priceAmount) {
  let amount = priceAmount === null ? 0 : priceAmount;

  // Remove commas if any.
  amount = amount.toString().replace(/,/g, '');
  amount = !Number.isNaN(Number(amount)) === true ? parseFloat(amount) : 0;

  return amount.toFixed(drupalSettings.alshaya_spc.currency_config.decimal_points);
};

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
exports.calculateDiscount = function (price, finalPrice) {
  const floatPrice = parseFloat(price);
  const floatFinalPrice = parseFloat(finalPrice);

  const discount = floatPrice - floatFinalPrice;
  if (floatPrice < 0.1 || floatFinalPrice < 0.1 || discount < 0.1) {
    return 0;
  }

  return parseFloat(Math.round((discount * 100) / floatPrice));
};
