import { isUserAuthenticated } from './helper';
import logger from './logger';
import { callMagentoApi } from './requestHelper';
import { hasValue } from './conditionsUtility';

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
    case 'checkBookingStatus':
      endpoint = '/V1/hfdbooking/check-hold-appointment-status'; // Endpoint to get appointment status, same for both.
      break;

    case 'getAvailableBookingSlots':
      endpoint = isUserAuthenticated()
        ? '/V1/hfdbooking/mine/available-time-slots'
        : `/V1/hfdbooking/${cartId}/available-time-slots`;
      break;

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
    hold_confirmation_number: confirmationNumber,
  });

  // @todo: Remove mock response after API integration.
  // Success = true scenario.
  response.data = {
    success: true,
    appointment_details: {
      appointment_date: '2022-05-27',
      start_time: '8:00 AM',
      end_time: '9:00 AM',
      appointment_date_time: '2022-05-27T08: 00: 00.000Z',
      resource_external_id: 'MorningShiftZone1KSA',
      hold_confirmation_number: 'G2Z7Y67B',
    },
  };

  // Success = false scenario.
  /* response.data = {
    success: false,
    error_message: 'The appointment already expired, Please select time slot again',
    appointment_details: {
      appointment_date: '2022-02-27',
      start_time: '8:00 AM',
      end_time: '9:00 AM',
      appointment_date_time: '2022-02-27T08: 00: 00.000Z',
      resource_external_id: 'MorningShiftZone1KSA',
      hold_confirmation_number: 'G2Z7Y67B',
    },
  }; */

  // Handle the error response from API in case of internal error.
  if (!hasValue(response.data)
    || (hasValue(response.data.error) && response.data.error)) {
    logger.warning('Online Booking: Error occurred while fetching booking details from confirmation number @confirmationNumber, API Response: @response.', {
      '@confirmationNumber': confirmationNumber,
      '@response': JSON.stringify(response.data),
    });
    return {
      success: false,
      error_message: response.data.error_message,
    };
  }

  // If booking is successful add confirmation number in result.
  if (hasValue(response.data.success)) {
    response.data.appointment_details.confirmation_number = confirmationNumber;
  } else {
    logger.warning('Online Booking: Api returns success false while fetching booking details from confirmation number @confirmationNumber, API Response: @response', {
      '@confirmationNumber': confirmationNumber,
      '@response': JSON.stringify(response.data),
    });
  }
  return response.data;
};

/**
 * Gets order booking response from magento api endpoint.
 *
 * @param {object} existingBooking
 *   Whether API call is made for existing booking.
 *
 * @returns {object}
 *   Returns the promise object.
 */
export const getAvailableBookingSlots = async (existingBooking = false) => {
  const response = await callMagentoApi(getOnlineBookingApiEndpoint('getAvailableBookingSlots'), 'GET');
  // @todo: Remove mock response after API integration.
  // Success = true scenario.
  response.data = {
    success: true,
    available_time_slots: [
      {
        appointment_date: '2022-05-27',
        appointment_slots: [
          {
            start_time: '8:00 AM',
            end_time: '9:00 AM',
            appointment_date_time: '2022-05-27T08:00:00.000Z',
            resource_external_id: 'MorningShiftZone1KSA',
          },
          {
            start_time: '3:00 PM',
            end_time: '4:00 PM',
            appointment_date_time: '2022-05-27T15:00:00.000Z',
            resource_external_id: 'EveningShiftZone1KSA',
          },
        ],
      },
      {
        appointment_date: '2022-06-04',
        appointment_slots: [
          {
            start_time: '8:00 AM',
            end_time: '9:00 AM',
            appointment_date_time: '2022-06-04T08:00:00.000Z',
            resource_external_id: 'MorningShiftZone1KSA',
          },
          {
            start_time: '9:30 AM',
            end_time: '10:30 AM',
            appointment_date_time: '2022-06-04T09:30:00.000Z',
            resource_external_id: 'EveningShiftZone2KSA',
          },
          {
            start_time: '10:30 AM',
            end_time: '11:00 AM',
            appointment_date_time: '2022-06-04T10:30:00.000Z',
            resource_external_id: 'MorningShiftZone3KSA',
          },
          {
            start_time: '11:00 AM',
            end_time: '11:30 AM',
            appointment_date_time: '2022-06-04T11:00:00.000Z',
            resource_external_id: 'EveningShiftZone4KSA',
          },
          {
            start_time: '12:00 PM',
            end_time: '12:30 PM',
            appointment_date_time: '2022-06-04T12:00:00.000Z',
            resource_external_id: 'MorningShiftZone5KSA',
          },
          {
            start_time: '12:30 PM',
            end_time: '01:00 PM',
            appointment_date_time: '2022-06-04T12:30:00.000Z',
            resource_external_id: 'EveningShiftZone6KSA',
          },
          {
            start_time: '02:00 PM',
            end_time: '04:00 PM',
            appointment_date_time: '2022-06-04T14:00:00.000Z',
            resource_external_id: 'MorningShiftZone7KSA',
          },
        ],
      },
      {
        appointment_date: '2022-06-15',
        appointment_slots: [
          {
            start_time: '8:00 AM',
            end_time: '9:00 AM',
            appointment_date_time: '2022-06-15T08:00:00.000Z',
            resource_external_id: 'MorningShiftZone1KSA',
          },
          {
            start_time: '10:00 AM',
            end_time: '12:00 PM',
            appointment_date_time: '2022-06-15T10:00:00.000Z',
            resource_external_id: 'MorningShiftZone3KSA',
          },
          {
            start_time: '02:00 PM',
            end_time: '4:00 PM',
            appointment_date_time: '2022-06-15T14:00:00.000Z',
            resource_external_id: 'EveningShiftZone4KSA',
          },
        ],
      },
      {
        appointment_date: '2022-06-28',
        appointment_slots: [
          {
            start_time: '8:00 AM',
            end_time: '9:00 AM',
            appointment_date_time: '2022-06-28T08:00:00.000Z',
            resource_external_id: 'MorningShiftZone1KSA',
          },
        ],
      },
      {
        appointment_date: '2022-07-01',
        appointment_slots: [
          {
            start_time: '8:00 AM',
            end_time: '9:00 AM',
            appointment_date_time: '2022-07-01T08:00:00.000Z',
            resource_external_id: 'MorningShiftZone1KSA',
          },
          {
            start_time: '1:00 PM',
            end_time: '2:00 PM',
            appointment_date_time: '2022-07-01T08:00:00.000Z',
            resource_external_id: 'AfernoonShiftZone1KSA',
          },
        ],
      },
      {
        appointment_date: '2022-07-15',
        appointment_slots: [
          {
            start_time: '8:00 AM',
            end_time: '9:00 AM',
            appointment_date_time: '2022-07-15T08:00:00.000Z',
            resource_external_id: 'MorningShiftZone1KSA',
          },
          {
            start_time: '11:00 AM',
            end_time: '12:00 PM',
            appointment_date_time: '2022-07-15T11:00:00.000Z',
            resource_external_id: 'AfernoonShiftZone2KSA',
          },
          {
            start_time: '1:00 PM',
            end_time: '2:00 PM',
            appointment_date_time: '2022-07-15T13:00:00.000Z',
            resource_external_id: 'AfernoonShiftZone3KSA',
          },
          {
            start_time: '02:00 PM',
            end_time: '03:00 PM',
            appointment_date_time: '2022-07-15T14:00:00.000Z',
            resource_external_id: 'MorningShiftZone4KSA',
          },
        ],
      },
    ],
  };
  // Success = false scenario.
  /* response.data = {
    success: false,
    error_message: 'Something went wrong, unable to get available time slots',
    available_time_slots: {},
  }; */

  // Handle the error response from API in case of internal error.
  if (!hasValue(response.data)
    || (hasValue(response.data.error) && response.data.error)) {
    logger.warning('Online Booking: Error occurred while fetching available booking slots @confirmation, API Response: @response.', {
      '@response': JSON.stringify(response.data),
    });
    return {
      success: false,
      api_error: true,
      error_message: response.data.error_message,
    };
  }

  // If its a new booking, set api_error to true to not to show error message.
  if (!existingBooking && !hasValue(response.data.success)) {
    logger.warning('Online Booking: Api returns success false while fetching available booking slots Response: @response, API Params: @params', {
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
  const response = await callMagentoApi(getOnlineBookingApiEndpoint('holdBookingSlot'), 'POST', params);
  // @todo: Remove mock response after API integration.
  // Success = true scenario.
  response.data = {
    success: true,
    hold_appointment: {
      confirmation_number: 'G2Z7Y67B',
    },
  };
  // Success = false scenario.
  /* response.data = {
    success: false,
    hold_appointment: {},
    error_message: 'Something went wrong,
     unable to Hold Appointment. Please select different time slot',
  }; */

  // Default error response if success is false.
  const errorResponse = {
    success: false,
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
  if (!hasValue(response.data.success)) {
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
export const hideOnlineBooking = () => hasValue(Drupal.getItemFromLocalStorage('hide-online-booking'));

/**
 * Store status of online booking for user.
 *
 * @param {boolean} status
 *   Set status of online booking for user.
 */
export const setHideOnlineBooking = (status = true) => {
  Drupal.removeItemFromLocalStorage('hide-online-booking');
  if (hasValue(status)) {
    Drupal.addItemInLocalStorage('hide-online-booking', status);
  }
};
