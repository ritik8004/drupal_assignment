import { isUserAuthenticated } from './helper';
import logger from './logger';
import { callMagentoApi } from './requestHelper';
import { hasValue } from './conditionsUtility';
import { getDefaultErrorMessage } from './error';

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
  let bookingDetails = {};
  const response = await callMagentoApi(getOnlineBookingApiEndpoint('checkBookingStatus'), 'POST', {
    hold_confirmation_number: confirmationNumber,
  });

  // @todo: Remove mock response after API integration.
  // Success = true scenario.
  response.data = {
    success: true,
    appointment_date: '2022-02-27',
    start_time: '8:00 AM',
    end_time: '9:00 AM',
    appointment_date_time: '2022-02-27T08: 00: 00.000Z',
    resource_external_id: 'MorningShiftZone1KSA',
    hold_confirmation_number: 'G2Z7Y67B',
  };
  // Success = false scenario.
  /* response.data = {
    success: false,
    error_message: 'The appointment already expired, Please select time slot again',
  }; */

  if (hasValue(response.data.error) && response.data.error) {
    logger.warning('Online Booking: Error occurred while fetching booking details from confirmation number @confirmation, API Response: @response.', {
      '@confirmation': confirmationNumber,
      '@response': JSON.stringify(response.data),
    });
    return {
      success: false,
      message: getDefaultErrorMessage(),
    };
  }

  if (hasValue(response.data) && hasValue(response.data.success)) {
    bookingDetails = response.data.appointment_details;
    bookingDetails.success = true;
  } else {
    bookingDetails = {
      success: false,
      message: response.data.error_message,
    };
  }
  return bookingDetails;
};

/**
 * Gets order booking response from magento api endpoint.
 *
 * @returns {object}
 *   Returns the promise object.
 */
export const getAvailableBookingSlots = async () => {
  const response = await callMagentoApi(getOnlineBookingApiEndpoint('getAvailableBookingSlots'), 'GET');
  // @todo: Remove mock response after API integration.
  // Success = true scenario.
  response.data = {
    success: true,
    available_time_slots: [
      {
        appointment_date: '2022-02-27',
        appointment_slots: [
          {
            start_time: '8:00 AM',
            end_time: '9:00 AM',
            appointment_date_time: '2022-02-27T08:00:00.000Z',
            resource_external_id: 'MorningShiftZone1KSA',
          },
          {
            start_time: '3:00 PM',
            end_time: '4:00 PM',
            appointment_date_time: '2022-02-27T15:00:00.000Z',
            resource_external_id: 'EveningShiftZone1KSA',
          },
        ],
      },
      {
        appointment_date: '2022-02-28',
        appointment_slots: [
          {
            start_time: '8:00 AM',
            end_time: '9:00 AM',
            appointment_date_time: '2022-02-28T08:00:00.000Z',
            resource_external_id: 'MorningShiftZone1KSA',
          },
        ],
      },
      {
        appointment_date: '2022-03-01',
        appointment_slots: [
          {
            start_time: '8:00 AM',
            end_time: '9:00 AM',
            appointment_date_time: '2022-03-01T08:00:00.000Z',
            resource_external_id: 'MorningShiftZone1KSA',
          },
          {
            start_time: '1:00 PM',
            end_time: '2:00 PM',
            appointment_date_time: '2022-03-01T08:00:00.000Z',
            resource_external_id: 'AfernoonShiftZone1KSA',
          },
        ],
      },
    ],
  };
  // Success = false scenario.
  /* response.data = {
    success: false,
    error_message: 'Something went wrong, unable to get available time slots',
  }; */

  if (hasValue(response.data) && hasValue(response.data.success)) {
    if (hasValue(response.data.available_time_slots)) {
      return {
        success: true,
        availableSlots: response.data.available_time_slots,
      };
    }
  }
  if (hasValue(response.data.error) && response.data.error) {
    logger.warning('Online Booking: Error occurred while fetching available booking slots @confirmation, API Response: @response.', {
      '@response': JSON.stringify(response.data),
    });
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
  let holdBookingDetails = {};
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
    error_message: 'Something went wrong,
     unable to Hold Appointment. Please select different time slot',
  }; */

  if (hasValue(response.data.error) && response.data.error) {
    logger.warning('Online Booking: Error occurred while holding booking slot API Response: @response, API Params: @params', {
      '@response': JSON.stringify(response.data),
      '@params': JSON.stringify(params),
    });
    // Check if the params have existing confirmation number.
    if (hasValue(params.existing_hold_confirmation_number)) {
      return {
        success: false,
        error_message: getDefaultErrorMessage(),
      };
    }
    return {
      error: true,
      error_message: getDefaultErrorMessage(),
    };
  }

  // Check if API returns success response.
  holdBookingDetails = response.data;
  if (hasValue(response.data) && hasValue(response.data.success)) {
    holdBookingDetails = {
      success: true,
      confirmation_number: response.data.hold_appointment.confirmation_number,
    };
  } else if (!hasValue(params.existing_hold_confirmation_number)) {
    holdBookingDetails = {
      error: true,
      error_message: getDefaultErrorMessage(),
    };
  }
  return holdBookingDetails;
};
