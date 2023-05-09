import Axios from 'axios';
import qs from 'qs';
import { cartErrorCodes, getDefaultErrorMessage, getProcessedErrorMessage } from './error';
import { hasValue, isArray } from './conditionsUtility';
import logger from './logger';
import { isUserAuthenticated } from './helper';
import { isEgiftCardEnabled } from './util';

/**
 * Wrapper to get cart settings.
 *
 * @param {string} key
 *   The key for the configuration.
 * @returns {(number|string!Object!Array)}
 *   Returns the configuration.
 */
const getCartSettings = (key) => window.drupalSettings.cart[key];

/**
 * Get the complete path for the Magento API.
 *
 * @param {string} path
 *  The API path.
 */
const i18nMagentoUrl = (path) => `${getCartSettings('url')}${path}`;

const logApiStats = (response) => {
  try {
    if (!hasValue(response) || !hasValue(response.config) || !hasValue(response.config.headers)) {
      return response;
    }

    const transferTime = Date.now() - response.config.headers.RequestTime;
    logger.debug('Finished API request @url in @transferTime, ResponseCode: @responseCode, Method: @method.', {
      '@url': response.config.url,
      '@transferTime': transferTime,
      '@responseCode': response.status,
      '@method': response.config.method,
    });
  } catch (error) {
    logger.error('Failed to log API response time, error: @message', {
      '@message': error.message,
    });
  }

  return response;
};

/**
 * Logs API response to the logging system.
 *
 * @param {string} type
 *   The type of log message.
 * @param {*} message
 *   The message text.
 * @param {*} statusCode
 *   The API response status code.
 * @param {*} code
 *   The code value in the response.
 */
const logApiResponse = (type, message, statusCode, code) => {
  logger[type]('Commerce backend call failed. Response Code: @responseCode, Error Code: @resultCode, Exception: @message.', {
    '@responseCode': statusCode,
    '@resultCode': hasValue(code) ? code : '-',
    '@message': hasValue(message) ? message : '-',
  });
};

/**
 * Helper to detect Captcha.
 *
 * @param <object> response
 *   The API response.
 */
const detectCaptcha = (response) => {
  // Check status code.
  if (response.status !== 403) {
    return;
  }

  // Check that content contains a string.
  if (response.data.indexOf('captcha-bypass') < 0) {
    return;
  }

  // Log.
  logger.debug('API response contains Captcha.');
};

/**
 * Helper to detect CloudFlare javascript challenge.
 *
 * @param <object> response
 *   The API response.
 */
const detectCFChallenge = (response) => {
  // Check status code.
  if (response.status !== 503) {
    return;
  }

  // Check that content contains a string.
  if (response.data.indexOf('DDos') < 0) {
    return;
  }

  // Log.
  logger.debug('API response contains CF Challenge.');
};

/**
 * Handle errors and messages.
 *
 * @param {Promise} apiResponse
 *   The response from the API.
 *
 * @returns {Promise}
 *   Returns a promise object.
 */
const handleResponse = (apiResponse) => {
  logApiStats(apiResponse);
  const response = {};
  response.data = {};
  response.status = apiResponse.status;

  // In case we don't receive any response data.
  if (typeof apiResponse.data === 'undefined') {
    logApiResponse('warning', 'Error while doing MDC api. Response result is empty.', apiResponse.status);

    const error = {
      data: {
        error: true,
        error_code: 500,
        error_message: getDefaultErrorMessage(),
      },
    };
    return new Promise((resolve) => resolve(error));
  }

  // If the response contains Captcha, the page will be reloaded once per session.
  detectCaptcha(apiResponse);
  // If the response contains a CF Challenge, the page will be reloaded once per session.
  detectCFChallenge(apiResponse);
  // Treat each status code.
  if (apiResponse.status === 202) {
    // Place order can return 202, this isn't error.
    // Do nothing here, we will let code below return the response.
  } else if (apiResponse.status === 500) {
    logApiResponse('warning', getProcessedErrorMessage(apiResponse), apiResponse.status);

    // Server error responses.
    response.data.error = true;
    response.data.error_code = 500;
  } else if (apiResponse.status > 500) {
    // Server error responses.
    response.data.error = true;
    response.data.error_code = 600;
    logApiResponse('warning', apiResponse.data.error_message, apiResponse.status);
  } else if (apiResponse.status === 401) {
    if (isUserAuthenticated()) {
      // Customer Token expired.
      logApiResponse('warning', `Got 401 response, redirecting to user/logout. ${apiResponse.data.message}`, apiResponse.status);

      // Log the user out and redirect to the login page.
      window.location = Drupal.url('user/logout');

      // Throw an error to prevent further javascript execution.
      throw new Error('The customer token is invalid.');
    }

    response.data.error = true;
    response.data.error_code = 401;
    logApiResponse('warning', apiResponse.data.message, apiResponse.status);
  } else if (apiResponse.status !== 200) {
    // Set default values.
    response.data.error = true;
    response.data.error_message = getDefaultErrorMessage();

    // Check for empty resonse data.
    if (!hasValue(apiResponse) || !hasValue(apiResponse.data)) {
      logApiResponse('warning', 'Error while doing MDC api. Response result is empty', apiResponse.status);
      response.data.error_code = 500;
    } else if (apiResponse.status === 404
      && !hasValue(apiResponse.data)
      && hasValue(apiResponse.message)) {
      response.data.code = 404;
      response.data.error_code = 404;
      response.data.error_message = response.message;

      // Log the error message.
      logApiResponse('warning', response.data.error_message, apiResponse.status, response.data.code);
    } else if (hasValue(apiResponse.data.message)) {
      // Process message.
      response.data.error_message = getProcessedErrorMessage(apiResponse);

      // Log the error message.
      logApiResponse('warning', response.data.error_message, apiResponse.status, apiResponse.data.code);

      // The following case happens when there is a stock mismatch between
      // Magento and OMS.
      if (apiResponse.status === 400
        && typeof apiResponse.data.code !== 'undefined'
        && apiResponse.data.code === cartErrorCodes.cartCheckoutQuantityMismatch) {
        response.data.code = cartErrorCodes.cartCheckoutQuantityMismatch;
        response.data.error_code = cartErrorCodes.cartCheckoutQuantityMismatch;
      } else if (apiResponse.status === 400
        && typeof apiResponse.data.code === 'undefined') {
        response.data.code = cartErrorCodes.cartHasUserError;
        response.data.error_code = cartErrorCodes.cartHasUserError;
      } else if (apiResponse.status === 404) {
        response.data.error_code = 404;
      } else {
        response.data.error_code = 500;
      }
    } else if (hasValue(apiResponse.data.messages)
      && hasValue(apiResponse.data.messages.error)
      && hasValue(response.data.messages.error)
    ) {
      // Other messages.
      const error = apiResponse.data.messages.error[0];
      logApiResponse('info', error.message, apiResponse.status, error.code);
      response.data.error_code = error.code;
      response.data.error_message = error.message;
    }
  } else if (typeof apiResponse.data.messages !== 'undefined'
    && typeof apiResponse.data.messages.error !== 'undefined') {
    const error = apiResponse.data.messages.error.shift();
    response.data.error = true;
    response.data.error_code = error.code;
    response.data.error_message = error.message;
    logApiResponse('info', error.message, apiResponse.status, error.code);
  } else if (isArray(apiResponse.data.response_message)
    && hasValue(apiResponse.data.response_message[1])
    && apiResponse.data.response_message[1] === 'error') {
    // When there is error in response_message from custom updateCart API.
    response.data.error = true;
    response.data.error_code = 400;
    [response.data.error_message] = apiResponse.data.response_message;
    logApiResponse('info', JSON.stringify(response.data.response_message), apiResponse.status, response.data.error_code);
  }

  // Assign response data as is if no error.
  if (typeof response.data.error === 'undefined') {
    response.data = JSON.parse(JSON.stringify(apiResponse.data));
  } else if (apiResponse.status > 400 && apiResponse.status < 700) {
    // Format error for specific cases so that in the front end we show user
    // friendly error messages.
    response.data.error_message = getDefaultErrorMessage();
  }

  return new Promise((resolve) => resolve(response));
};

/**
 * Get Magento API params.
 *
 * @param {string} url
 *   The url to send the request to.
 * @param {string} method
 *   The request method.
 * @param {object} data
 *   The object to send for POST request.
 * @param {object} useBearerToken
 *   Flag to use bearer token or not.
 *
 * @returns {object}
 *   Returns a params object.
 */
const getMagentoApiParams = (url, method = 'GET', data = {}, useBearerToken = true) => {
  const params = {
    url: i18nMagentoUrl(url),
    method,
    headers: {
      'Content-Type': 'application/json',
      'Alshaya-Channel': 'web',
    },
  };

  if (isUserAuthenticated() && useBearerToken) {
    params.headers.Authorization = `Bearer ${window.drupalSettings.userDetails.customerToken}`;
  }

  if (typeof data !== 'undefined' && data && Object.keys(data).length > 0) {
    if (method.toUpperCase() === 'GET') {
      params.params = data;
    } else {
      params.data = data;
    }
  }

  // Add digital cart id to the params for get-cart calls
  // if top-up masked id exists in local storage.
  if (isEgiftCardEnabled() && (url.indexOf('getCart') > -1
    || url.indexOf('payment-methods') > -1
    || url.indexOf('selected-payment-method') > -1
    || url.indexOf('tabby-available-products') > -1)) {
    const topUpQuote = Drupal.getItemFromLocalStorage('topupQuote');
    if (topUpQuote !== null) {
      params.params = {
        digitalcart_id: topUpQuote.maskedQuoteId,
      };
    }
  }

  params.headers = params.headers || {};
  params.headers.RequestTime = Date.now();
  return params;
};

/**
 * Make an AJAX call to Magento API.
 *
 * @param {string} url
 *   The url to send the request to.
 * @param {string} method
 *   The request method.
 * @param {object} data
 *   The object to send for POST request.
 * @param {object} useBearerToken
 *   Flag to use bearer token or not.
 *
 * @returns {Promise<AxiosPromise<object>>}
 *   Returns a promise object.
 */
const callMagentoApi = (url, method = 'GET', data = {}, useBearerToken = true) => {
  const params = getMagentoApiParams(url, method, data, useBearerToken);
  return Axios(params)
    .then((response) => handleResponse(response))
    .catch((error) => {
      if (error.response) {
        // The request was made and the server responded with a status code
        // that falls out of the range of 2xx
        return handleResponse(error.response);
      }
      if (error.request) {
        // The request was made but no response was received
        return handleResponse(error.request);
      }

      logger.error('Something happened in setting up the request that triggered an error: @message.', {
        '@message': error.message,
      });

      return error;
    });
};

/**
 * Make an synchronous AJAX call to Magento API.
 *
 * @param {string} url
 *   The url to send the request to.
 * @param {string} method
 *   The request method.
 * @param {mixed} data
 *   The object to send for POST request.
 *
 * @returns {object}
 *   Returns a ajax response.
 */
const callMagentoApiSynchronous = (url, method = 'GET', data = {}) => {
  const params = getMagentoApiParams(url, method, data);
  let result;
  jQuery.ajax({
    url: params.url,
    type: method,
    async: false,
    cache: false,
    data: params.data,
    headers: params.headers,
    success(response) {
      result = response;
    },
    error(exception) {
      logger.error('Something happened in setting up the request that triggered an error: @message.', {
        '@message': exception.responseText,
      });
      result = exception;
    },
  });
  return result;
};

/**
 * Make an AJAX call to Drupal API.
 *
 * @param {string} url
 *   The url to send the request to.
 * @param {string} method
 *   The request method.
 * @param {object} data
 *   The object to send with the request.
 *
 * @returns {Promise<AxiosPromise<object>>}
 *   Returns a promise object.
 */
const callDrupalApi = (url, method = 'GET', data = {}) => {
  const headers = {};
  const params = {
    url: `/${window.drupalSettings.path.currentLanguage}${url}`,
    method,
    data,
  };

  if (typeof data !== 'undefined' && data && Object.keys(data).length > 0) {
    Object.keys(data).forEach((optionName) => {
      if (optionName === 'form_params') {
        headers['Content-Type'] = 'application/x-www-form-urlencoded';
        params.data = qs.stringify(data[optionName]);
      }
    });
  }

  params.headers = params.headers || {};
  params.headers.RequestTime = Date.now();

  return Axios(params)
    .then((response) => logApiStats(response))
    .catch((error) => {
      if (hasValue(error.response) && hasValue(error.response.status)) {
        logApiStats(error.response);
        const responseCode = parseInt(error.response.status, 10);

        if (responseCode === 404) {
          logger.warning('Drupal page no longer available.', { ...params });
          return null;
        }

        if (responseCode === 400) {
          logger.warning('Drupal API call failed.', {
            responseCode,
            ...params,
          });
        } else {
          logger.error('Drupal API call failed.', {
            responseCode,
            ...params,
          });
        }
        return null;
      }

      logger.error('Something happened in setting up the request that triggered an error: @message.', {
        '@message': error.message,
        ...params,
      });

      return null;
    });
};

/**
 * Helper function to prepare the data.
 *
 * @param array $filters
 *   Array containing all filters, must contain field and value, can contain
 *   condition_type too or all that is supported by Magento.
 * @param string $base
 *   Filter Base, mostly searchCriteria.
 * @param int $group_id
 *   Filter group id, mostly 0.
 *
 * @return object
 *   Prepared data.
 */
const prepareFilterData = (filters, base = 'searchCriteria', groupId = 0) => {
  const data = {};

  filters.forEach((filter, index) => {
    Object.keys(filter).forEach((key) => {
      // Prepare string like below.
      // searchCriteria[filter_groups][0][filters][0][field]=field
      // This is how Magento search criteria in APIs work.
      data[`${base}[filter_groups][${groupId}][filters][${index}][${key}]`] = filter[key];
    });
  });

  return data;
};

export {
  callDrupalApi,
  callMagentoApi,
  getCartSettings,
  callMagentoApiSynchronous,
  prepareFilterData,
};
