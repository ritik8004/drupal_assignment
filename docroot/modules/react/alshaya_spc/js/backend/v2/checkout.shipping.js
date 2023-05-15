import md5 from 'md5';
import {
  getApiEndpoint,
  isRequestFromSocialAuthPopup,
} from './utility';
import logger from '../../../../js/utilities/logger';
import { getFormattedError } from './common';
import { hasValue, isString } from '../../../../js/utilities/conditionsUtility';
import { callMagentoApi } from '../../../../js/utilities/requestHelper';

/**
 * Format the address array.
 *
 * Format the address array so that it can be used to update billing or
 * shipping address in the cart.
 *
 * @param {object} address
 *   Address array.
 * @return {object}.
 *   Formatted address object.
 */
const formatAddressForShippingBilling = (address) => {
  // Return as is if custom_attributes already set.
  if (typeof address.custom_attributes !== 'undefined') {
    return address;
  }

  const data = {};

  if (hasValue(address.static)) {
    Object.keys(address.static).forEach((key) => {
      data[key] = address.static[key];
    });
  }

  data.street = isString(address.street)
    ? [address.street]
    : address.street;

  const customAttributes = [];
  Object.keys(address).forEach((key) => {
    if (typeof data[key] !== 'undefined' || key === 'carrier_info' || key === 'static') {
      return;
    }

    if (!hasValue(address[key])) {
      return;
    }

    customAttributes.push({
      attribute_code: key,
      value: address[key],
    });
  });

  data.custom_attributes = customAttributes;
  return data;
};

/**
 * Format address structure for shipping estimates api.
 *
 * @param {object} $address
 *   Address object.
 * @return {object}.
 *   Formatted address object.
 */
const formatShippingEstimatesAddress = (address) => {
  const data = {};
  data.firstname = (typeof address.firstname !== 'undefined') ? address.firstname : '';
  data.lastname = (typeof address.lastname !== 'undefined') ? address.lastname : '';
  data.email = (typeof address.email !== 'undefined') ? address.email : '';
  data.country_id = (typeof address.country_id !== 'undefined') ? address.country_id : '';
  data.city = (typeof address.city !== 'undefined') ? address.city : '';
  data.telephone = (typeof address.telephone !== 'undefined') ? address.telephone : '';

  data.street = isString(address.street)
    ? [address.street]
    : address.street;

  data.custom_attributes = [];
  if (typeof address.custom_attributes !== 'undefined' && address.custom_attributes.length > 0) {
    data.custom_attributes = address.custom_attributes.map((item) => {
      if (typeof item.value !== 'undefined' && item.value !== '') {
        return {
          attribute_code: item.attribute_code,
          value: item.value,
        };
      }
      return null;
    }).filter((item) => (item !== null));
  }

  // If custom attributes not available, we check for extension attributes.
  if (data.custom_attributes.length === 0 && typeof address.extension_attributes !== 'undefined' && Object.keys(address.extension_attributes).length > 0) {
    Object.keys(address.extension_attributes).forEach((key) => {
      data.custom_attributes.push(
        {
          attribute_code: key,
          value: address.extension_attributes[key],
        },
      );
    });
  }

  // Sort custom_attributes to make sure it is always in same order.
  data.custom_attributes.sort((a, b) => {
    if (a.attribute_code < b.attribute_code) {
      return -1;
    }

    if (a.attribute_code > b.attribute_code) {
      return 1;
    }

    return 0;
  });

  return data;
};

/**
 * Gets shipping methods.
 *
 * @param data
 *   The shipping address.
 *
 * @returns {Promise<object>}
 *   HD Shipping methods or error.
 */
const getHomeDeliveryShippingMethods = async (data) => {
  // If request is from SocialAuth Popup, restrict further processing.
  // we don't want magento API calls happen on popup, As this is causing issues
  // in processing parent pages.
  if (isRequestFromSocialAuthPopup()) {
    return getFormattedError(600, 'fetching shipping methods on socialAuth popup is not allowed');
  }

  if (!hasValue(data.country_id)) {
    logger.error('Error in getting shipping methods for HD as country id not available. Data: @data', {
      '@data': JSON.stringify(data),
    });

    return getFormattedError(600, 'Error in getting shipping methods');
  }

  // Prepare address data for api call.
  const formattedAddress = formatShippingEstimatesAddress(data);

  // Create a key for static storage.
  const key = md5(JSON.stringify(formattedAddress.custom_attributes));

  // Get shipping methods from static.
  const staticShippingMethods = Drupal.alshayaSpc.staticStorage.get('shipping_methods') || {};

  if (!hasValue(staticShippingMethods[key])) {
    staticShippingMethods[key] = [];
    const cartId = window.commerceBackend.getCartId();

    const url = getApiEndpoint('estimateShippingMethods', { cartId });
    const response = await callMagentoApi(url, 'POST', { address: formattedAddress });

    // Check for errors.
    if (hasValue(response.data.error) && response.data.error) {
      logger.warning('Error in getting shipping methods for HD. Error: @message', {
        '@message': response.data.error_message,
      });

      return getFormattedError(response.data.error_code, response.data.error_message);
    }

    if (!hasValue(response.data)) {
      const message = 'Got empty response while getting shipping methods for HD.';
      logger.notice(message);

      return getFormattedError(600, message);
    }

    // Add log for shipping methods for HD that we get from magento.
    logger.notice('Shipping methods for HD. CartId: @cartId, Url: @url, Response: @response.', {
      '@cartId': cartId,
      '@url': url,
      '@response': JSON.stringify(response.data),
    });

    // Delete methods for CNC.
    const methods = response.data.filter((i) => i.carrier_code !== 'click_and_collect');

    if (!hasValue(methods)) {
      const message = 'No shipping methods available for HD.';
      logger.notice(message);

      return getFormattedError(600, message);
    }

    // Set shipping methods in static.
    staticShippingMethods[key] = Object.values(methods);
    Drupal.alshayaSpc.staticStorage.set('shipping_methods', staticShippingMethods);
  }

  return {
    error: false,
    methods: staticShippingMethods[key],
  };
};

export {
  formatAddressForShippingBilling,
  getHomeDeliveryShippingMethods,
};
