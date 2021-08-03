import _isEmpty from 'lodash/isEmpty';
import _isUndefined from 'lodash/isUndefined';
import _isString from 'lodash/isString';
import md5 from 'md5';
import { getApiEndpoint, logger } from './utility';
import { callMagentoApi } from './common';
import StaticStorage from './staticStorage';


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

  if (!_isEmpty(address.static)) {
    Object.keys(address.static).forEach((key) => {
      data[key] = address.static[key];
    });
  }

  data.street = _isString(address.street)
    ? [address.street]
    : address.street;

  const customAttributes = [];
  Object.keys(address).forEach((key) => {
    if (typeof data[key] !== 'undefined' || key === 'carrier_info' || key === 'static') {
      return;
    }

    if (_isEmpty(address[key])) {
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

  data.street = _isString(address.street)
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

  return data;
};

/**
 * Gets shipping methods.
 *
 * @param data
 *   The shipping address.
 *
 * @returns {Promise<array>}
 *   HD Shipping methods.
 */
const getHomeDeliveryShippingMethods = async (data) => {
  if (_isEmpty(data.country_id)) {
    logger.error(`Error in getting shipping methods for HD as country id not available. Data: ${JSON.stringify(data)}`);
    return [];
  }

  // Prepare address data for api call.
  const formattedAddress = formatShippingEstimatesAddress(data);

  // Create a key for static storage.
  const key = md5(JSON.stringify(formattedAddress.custom_attributes));

  // Get shipping methods from static.
  const staticShippingMethods = StaticStorage.get('shipping_methods') || {};
  if (!_isEmpty(staticShippingMethods[key])) {
    return staticShippingMethods[key];
  }

  staticShippingMethods[key] = [];
  const url = getApiEndpoint('estimateShippingMethods', { cartId: window.commerceBackend.getCartId() });
  const response = await callMagentoApi(url, 'POST', { address: formattedAddress });
  if (!_isEmpty(response.data)) {
    const methods = response.data;

    // Check for errors.
    if (!_isUndefined(methods.error) && methods.error) {
      logger.error(`Error in getting shipping methods for HD. Data: ${methods.error_message}`);
      return methods;
    }

    // Delete CNC from methods.
    for (let i = 0; i < methods.length; i++) {
      if (methods[i].carrier_code === 'click_and_collect') {
        delete methods[i];
      }
    }

    // Set shipping methods in static.
    staticShippingMethods[key] = Object.values(methods);
    StaticStorage.set('shipping_methods', staticShippingMethods);
  }

  // Return methods.
  return staticShippingMethods[key];
};

export {
  formatAddressForShippingBilling,
  getHomeDeliveryShippingMethods,
};
