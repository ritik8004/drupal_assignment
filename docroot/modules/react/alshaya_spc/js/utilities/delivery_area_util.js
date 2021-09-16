import hasValue from '../../../js/utilities/conditionsUtility';
import { removeFullScreenLoader } from './checkout_util';
import getStringMessage from './strings';

export const getGovernatesList = () => window.commerceBackend.getGovernatesList()
  .then(
    (responseData) => {
      if (typeof responseData !== 'object') {
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
      if (typeof responseData !== 'object') {
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

export const getCartShippingMethods = (currentArea) => window.commerceBackend.getCartShippingMethod(
  currentArea,
)
  .then(
    (responseData) => {
      if (typeof responseData !== 'object') {
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
    Drupal.logJavascriptError('get-cart-shipping-methods', error, GTM_CONSTANTS.CHECKOUT_ERRORS);
  });
