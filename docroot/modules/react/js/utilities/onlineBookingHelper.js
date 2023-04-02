import { isUserAuthenticated } from './helper';
import logger from './logger';
import { callMagentoApi, callMagentoApiSynchronous } from './requestHelper';
import { hasValue } from './conditionsUtility';
import getStringMessage from './strings';

/**
 * Gets magento api endpoint by user role.
 *
 * @param {string} action
 *   Callname for the API.
 *
 * @returns {string}
 *   The api endpoint.
 */
export const getOnlineBookingApiEndpoint = (action) => {
  let endpoint = '';
  const cartId = window.commerceBackend.getCartId();
  switch (action) {
    // Endpoint to check get status of appointment hold for user.
    case 'checkBookingStatus':
      endpoint = isUserAuthenticated()
        ? '/V1/hfdbooking/mine/check-hold-appointment-status'
        : `/V1/hfdbooking/${cartId}/check-hold-appointment-status`;
      break;

    // Endpoint to get all available time slots for the address.
    case 'getAvailableBookingSlots':
      endpoint = isUserAuthenticated()
        ? '/V1/hfdbooking/mine/available-time-slots'
        : `/V1/hfdbooking/${cartId}/available-time-slots`;
      break;

    // Endpoint to hold the time slot for online booking.
    case 'holdBookingSlot':
      endpoint = isUserAuthenticated()
        ? '/V1/hfdbooking/mine/hold-appointment'
        : `/V1/hfdbooking/${cartId}/hold-appointment`;
      break;

    default:
      logger.critical('Endpoint does not exist for online booking action: @action.', {
        '@action': action,
      });
  }

  return endpoint;
};

/**
 * Gets online booking details using confirmation number.
 *
 * @returns {object}
 *   Returns the promise object.
 */
export const getBookingDetailByConfirmationNumber = async (confirmationNumber) => {
  const response = await callMagentoApi(getOnlineBookingApiEndpoint('checkBookingStatus'), 'POST', {
    confirmationNumber,
  });

  // Handle the error response from API in case of internal error.
  if (!hasValue(response.data)
    || (hasValue(response.data.error) && response.data.error)) {
    logger.warning('Online Booking: Error occurred while fetching booking details from confirmation number @confirmationNumber, API Response: @response.', {
      '@confirmationNumber': confirmationNumber,
      '@response': JSON.stringify(response.data),
    });
    return {
      status: false,
      error_message: response.data.error_message,
    };
  }

  // If booking is successful add confirmation number in result.
  if (!hasValue(response.data.status)) {
    logger.warning('Online Booking: Api returns success false while fetching booking details from confirmation number @confirmationNumber, API Response: @response', {
      '@confirmationNumber': confirmationNumber,
      '@response': JSON.stringify(response.data),
    });
  }
  return response.data;
};

/**
 * Gets online booking details using confirmation number with synchronous call
 * to Magento API.
 *
 * @returns {object}
 *   Returns the object.
 */
export const getBookingDetailByConfirmationNumberSynchronous = (confirmationNumber) => {
  const response = callMagentoApiSynchronous(getOnlineBookingApiEndpoint('checkBookingStatus'), 'POST', JSON.stringify({
    confirmationNumber,
  }));

  // Handle the error response from API in case of internal error.
  if (!hasValue(response)
    || (hasValue(response.error) && response.error)) {
    logger.warning('Online Booking: Error occurred while fetching booking details from confirmation number @confirmationNumber, API Response: @response.', {
      '@confirmationNumber': confirmationNumber,
      '@response': JSON.stringify(response),
    });
    return {
      status: false,
      error_message: response.error_message,
    };
  }

  // If booking is successful add confirmation number in result.
  if (!hasValue(response.status)) {
    logger.warning('Online Booking: Api returns success false while fetching booking details from confirmation number @confirmationNumber, API Response: @response', {
      '@confirmationNumber': confirmationNumber,
      '@response': JSON.stringify(response),
    });
  }
  return response;
};

/**
 * Get available time slots based on the address.
 *
 * @param {object} existingBooking
 *   Whether API call is made for existing booking.
 *
 * @returns {object}
 *   Returns the promise object.
 */
export const getAvailableBookingSlots = async (existingBooking = false) => {
  const response = await callMagentoApi(getOnlineBookingApiEndpoint('getAvailableBookingSlots'), 'GET');
  // Handle the error response from API in case of internal error.
  if (!hasValue(response.data)
    || (hasValue(response.data.error) && response.data.error)) {
    logger.warning('Online Booking: Error occurred while fetching available booking slots, API Response: @response.', {
      '@response': JSON.stringify(response.data),
    });
    return {
      status: false,
      api_error: true,
      error_message: response.data.error_message,
    };
  }

  // If its a new booking, set api_error to true to not to show error message.
  if (!existingBooking && !hasValue(response.data.status)) {
    logger.warning('Online Booking: Api returns success false while fetching available booking slots Response: @response', {
      '@response': JSON.stringify(response.data),
    });
    response.data.api_error = true;
  }

  return response.data;
};

/**
 * Hold booking slot for user.
 *
 * @param {object} params
 *   The booking details.
 *
 * @returns {object}
 *   Returns the promise object.
 */
export const holdBookingSlot = async (params) => {
  const appointmentDetails = {
    appointmentDetails: params,
  };
  const response = await callMagentoApi(getOnlineBookingApiEndpoint('holdBookingSlot'), 'POST', appointmentDetails);
  // Default error response if success is false.
  const errorResponse = {
    status: false,
  };

  // Handle the error response from API in case of internal error.
  if (!hasValue(response.data)
    || (hasValue(response.data.error) && response.data.error)) {
    logger.warning('Online Booking: Error occurred while holding booking slot API Response: @response, API Params: @params', {
      '@response': JSON.stringify(response.data),
      '@params': JSON.stringify(params),
    });
    // Check if the params have existing confirmation number.
    if (hasValue(params.existing_hold_confirmation_number)) {
      return errorResponse;
    }
    errorResponse.api_error = true;
    return errorResponse;
  }

  // If the success is false and its not existing booking.
  // then return the response with api_error.
  if (!hasValue(response.data.status)) {
    logger.warning('Online Booking: Api returns success false while holding booking slot API Response: @response, API Params: @params', {
      '@response': JSON.stringify(response.data),
      '@params': JSON.stringify(params),
    });
    if (!hasValue(params.existing_hold_confirmation_number)) {
      errorResponse.api_error = true;
      return errorResponse;
    }
  }

  return response.data;
};

/**
 * Decide whether to show online booking for user or not.
 *
 * While fetching the slots or holding the appointment date from MDC.
 * If any internal error occurred while calling API
 * We need to bypass the online booking for user as user
 * has not seen the online booking screen when user first visited the checkout page.
 */
export const getHideOnlineBooking = () => hasValue(Drupal.getItemFromLocalStorage('hide_online_booking'));

/**
 * Store status of online booking for user.
 *
 * @param {boolean} status
 *   Set status of online booking for user.
 */
export const setHideOnlineBooking = (status = true) => {
  Drupal.removeItemFromLocalStorage('hide_online_booking');
  if (hasValue(status)) {
    Drupal.addItemInLocalStorage('hide_online_booking', status);
  }
};

/**
 * Return the given time with translation of AM/PM string in that. Assuming
 * given time will follow the format like '10:00 AM' or '11:00 PM' strictly.
 *
 * @param {string} translatedTime
 *   Return the translated time.
 */
export const getTranslatedTime = (time = null) => {
  // Return if no time string provided.
  if (!time) {
    return null;
  }

  // We only have to tranlate if current language is not english. So while it
  // is english return the time string we received without any change assuming
  // the default language is english.
  if (drupalSettings.path.currentLanguage === 'en') {
    return time;
  }

  // Split the time by space to get AM/PM string separately.
  const timeArray = time.split(' ');
  // Check if the split array has more then one index item in array. We will
  // assume the 2nd item in the array will be AM/PM string as per time format.
  if (timeArray.length > 1 && typeof timeArray[1] === 'string') {
    // Translate the second item and re-assign in array on same position.
    timeArray[1] = getStringMessage(`online_booking_${timeArray[1].toLowerCase()}`);
    // Return time with translated data to show on FE.
    return timeArray.join(' ');
  }

  // Return time as received.
  return time;
};
