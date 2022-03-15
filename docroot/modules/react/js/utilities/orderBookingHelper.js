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
export const getApiEndpoint = (action) => {
  let endpoint = '';
  const cartId = window.commerceBackend.getCartId();
  switch (action) {
    case 'checkAppointmentStatus':
      endpoint = '/V1/hfdbooking/check-hold-appointment-status'; // Endpoint to get appointment status, same for both.
      break;

    case 'getAvailableTimeSlots':
      endpoint = isUserAuthenticated()
        ? '/V1/hfdbooking/mine/available-time-slots'
        : `/V1/hfdbooking/${cartId}/available-time-slots`;
      break;

    case 'holdAppointment':
      endpoint = isUserAuthenticated()
        ? '/V1/hfdbooking/mine/hold-appointment'
        : `/V1/hfdbooking/${cartId}/hold-appointment`;
      break;

    default:
      logger.critical('Endpoint does not exist for action: @action.', {
        '@action': action,
      });
  }

  return endpoint;
};

/**
 * Gets order booking response from magento api endpoint.
 *
 * @returns {object}
 *   Returns the promise object.
 */
export const checkAppointmentStatus = async (confirmationNumber) => {
  // eslint-disable-next-line no-unused-vars
  let appointmentDetails = {};
  const response = await callMagentoApi(getApiEndpoint('checkAppointmentStatus'), 'POST', {
    hold_confirmation_number: confirmationNumber,
  });
  if (hasValue(response.data) && hasValue(response.data.success)) {
    appointmentDetails = response.data.appointment_details;
  } else {
    logger.warning('Order Booking: Error while holding time slot. Response: @response', {
      '@response': JSON.stringify(response.data),
    });
  }

  // @todo: Remove default response when api integration finished.
  return {
    appointment_date: '2022-02-27',
    start_time: '8:00 AM',
    end_time: '9:00 AM',
    appointment_date_time: '2022-02-27T08: 00: 00.000Z',
    resource_external_id: 'MorningShiftZone1KSA',
    hold_confirmation_number: 'G2Z7Y67B',
  };
};

/**
 * Gets order booking response from magento api endpoint.
 *
 * @returns {object}
 *   Returns the promise object.
 */
export const getAvailableTimeSlots = async () => {
  // eslint-disable-next-line no-unused-vars
  let availableSlots = {};
  const response = await callMagentoApi(getApiEndpoint('getAvailableTimeSlots'), 'GET');
  if (hasValue(response.data) && hasValue(response.data.success)) {
    if (hasValue(response.data.available_time_slots)) {
      availableSlots = response.data.available_time_slots;
    }
  } else {
    logger.warning('Order Booking: Error while getting time slots. Response: @response', {
      '@response': JSON.stringify(response.data),
    });
  }
  // Return available response.
  // @todo: Remove default response when api integration finished.
  return [
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
  ];
};

/**
 * Gets order booking response from magento api endpoint.
 *
 * @param {object} params
 *   The appointment details.
 *
 * @returns {object}
 *   Returns the promise object.
 */
export const holdAppointment = async (params) => {
  const holdDetails = {};
  const response = await callMagentoApi(getApiEndpoint('holdAppointment'), 'POST', params);
  if (hasValue(response.data) && hasValue(response.data.success)) {
    holdDetails.confirmation_number = response.data.hold_appointment.confirmation_number;
  } else {
    logger.warning('Order Booking: Error while holding time slot. Response: @response', {
      '@response': JSON.stringify(response.data),
    });
  }

  // @todo: Remove default response when api integration finished.
  return {
    confirmation_number: 'G2Z7Y67B',
  };
};
