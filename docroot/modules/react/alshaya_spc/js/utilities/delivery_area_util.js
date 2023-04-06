import { hasValue } from '../../../js/utilities/conditionsUtility';
import { removeFullScreenLoader } from './checkout_util';
import getStringMessage from './strings';
import { getProductShippingMethods } from '../backend/v2/common';

export const getGovernatesList = () => window.commerceBackend.getGovernatesList()
  .then(
    (responseData) => {
      if (responseData === null || typeof responseData !== 'object') {
        removeFullScreenLoader();
        return null;
      }

      if (responseData && Object.keys(responseData).length === 0
        && Object.getPrototypeOf(responseData) === Object.prototype) {
        removeFullScreenLoader();
        return null;
      }

      if (hasValue(responseData.error)) {
        return responseData;
      }

      return responseData;
    },
    () => ({
      error: true,
      error_message: getStringMessage('global_error'),
    }),
  )
  .catch((error) => {
    Drupal.logJavascriptError('get-governates-list', error, GTM_CONSTANTS.CHECKOUT_ERRORS);
  });

export const getDeliveryAreaList = (governateId) => window.commerceBackend.getDeliveryAreaList(
  governateId,
)
  .then(
    (responseData) => {
      if (responseData === null || typeof responseData !== 'object') {
        removeFullScreenLoader();
        return null;
      }

      if (hasValue(responseData.error)) {
        return responseData;
      }

      return responseData;
    },
    () => ({
      error: true,
      error_message: getStringMessage('global_error'),
    }),
  )
  .catch((error) => {
    Drupal.logJavascriptError('get-delivery-areas', error, GTM_CONSTANTS.CHECKOUT_ERRORS);
  });

export const getCartShippingMethods = (currArea, sku, cartId) => getProductShippingMethods(
  currArea,
  sku,
  cartId,
)
  .then(
    (responseData) => {
      if (responseData === null || typeof responseData !== 'object') {
        removeFullScreenLoader();
        return null;
      }

      if (hasValue(responseData.error)) {
        return responseData;
      }

      return responseData;
    },
    () => ({
      error: true,
      error_message: getStringMessage('global_error'),
    }),
  )
  .catch((error) => {
    if (drupalSettings.path.currentPath === 'checkout') {
      Drupal.logJavascriptError('get-cart-shipping-methods', error, GTM_CONSTANTS.CHECKOUT_ERRORS);
    } else {
      Drupal.logJavascriptError('get-cart-shipping-methods', error);
    }
  });

export const getDeliveryAreaValue = (areaId) => window.commerceBackend.getDeliveryAreaValue(
  areaId,
)
  .then(
    (responseData) => {
      if (responseData === null || typeof responseData !== 'object') {
        removeFullScreenLoader();
        return null;
      }

      if (hasValue(responseData.error)) {
        return responseData;
      }

      return responseData;
    },
    () => ({
      error: true,
      error_message: getStringMessage('global_error'),
    }),
  )
  .catch((error) => {
    Drupal.logJavascriptError('get-delivery-area-value', error, GTM_CONSTANTS.CHECKOUT_ERRORS);
  });

/**
 * Fetching delivery area values choosen by user.
 */
export const getDeliveryAreaStorage = () => Drupal.getItemFromLocalStorage('deliveryinfo-areadata');

export const getAreaFieldKey = () => {
  if (hasValue(drupalSettings.address_fields)
    && hasValue(drupalSettings.address_fields.administrative_area)) {
    return drupalSettings.address_fields.administrative_area.key;
  }
  return null;
};

export const getAreaParentFieldKey = () => {
  if (hasValue(drupalSettings.address_fields)
    && hasValue(drupalSettings.address_fields.area_parent)) {
    return drupalSettings.address_fields.area_parent.key;
  }
  return null;
};

/**
 * Storing delivery area values choosen by user.
 */
export const setDeliveryAreaStorage = (areaSelected) => {
  const areaFieldKey = getAreaFieldKey();
  const areaParentFieldKey = getAreaParentFieldKey();
  if (areaFieldKey !== null && areaParentFieldKey !== null) {
    Drupal.removeItemFromLocalStorage('deliveryinfo-areadata');
    const { currentLanguage } = drupalSettings.path;
    const deliveryArea = {
      label: {
        [currentLanguage]: areaSelected.label,
      },
      value: {
        [areaFieldKey]: areaSelected.area,
        [areaParentFieldKey]: areaSelected.governate,
      },
    };
    Drupal.addItemInLocalStorage('deliveryinfo-areadata', deliveryArea);
  }
};
