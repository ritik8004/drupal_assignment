import { postAPIData, getAPIData } from '../../utilities/api/fetchApiData';

/**
 * Global object to help perform Aura activities for V2.
 */
window.auraBackend = window.auraBackend || {};

/**
 * Performs the final step of the signup process for the user.
 *
 * @param {object} data
 *   Object containing items like firstname, lastname, email and phone/mobile.
 *
 * @returns {Promise}
 *   The promise object which resolves to the response data and status in case
 * of success and the error object in case of error.
 */
window.auraBackend.loyaltyClubSignUp = (data) => postAPIData('post/loyalty-club/sign-up', data);

/**
 * Sends OTP.
 *
 * @param {string} mobile
 *   The mobile number.
 * @param {string} chosenCountryCode
 *   The country code value.
 *
 * @returns {Object}
 *   Return API response status.
 */
window.auraBackend.sendSignUpOtp = (mobile, chosenCountryCode) => postAPIData('post/loyalty-club/send-otp', {
  mobile,
  chosenCountryCode,
});

/**
 * Verifies the OTP entered by the user.
 *
 * @param {string} mobile
 *   Mobile number.
 * @param {string} otp
 *   Otp value.
 * @param {string} type
 *   Type of action for which otp is generated, eg. registration.
 * @param {string} chosenCountryCode
 *   The country code value.
 *
 * @returns {Promise}
 *   Returns an object with status value or the error object in case of failure.
 */
window.auraBackend.verifyOtp = (mobile, otp, type, chosenCountryCode) => postAPIData('post/loyalty-club/verify-otp', {
  mobile,
  otp,
  type,
  chosenCountryCode,
});

/**
 * Send Link card OTP.
 *
 * @param {string} type
 *   The field for searching.
 * @param {string} value
 *   The field value.
 *
 * @returns {Promise}
 *   Returns a promise which resolves to an object.
 * On error, the error object is returned.
 * On success, the success object is returned containing specific data.
 */
window.auraBackend.sendLinkCardOtp = (type, value) => postAPIData('post/loyalty-club/send-link-card-otp', {
  type,
  value,
});

/**
 * Fetches loyalty customer details for the current user.
 *
 * @returns {Promise}
 *   The promise object which resolves to the response data and status in case
 *   of success and the error object in case of error.
 */
window.auraBackend.getCustomerDetails = () => getAPIData('get/loyalty-club/get-customer-details');

/**
 * Fetches progress tracker for the current user.
 *
 * @returns {Promise}
 *   The promise object which resolves to the response data and status in case
 *   of success and the error object in case of error.
 */
window.auraBackend.getProgressTracker = () => getAPIData('get/loyalty-club/get-progress-tracker');

/**
 * Set/Unset loyalty card in cart.
 *
 * @param {string} action
 *   The action, eg. 'add'.
 * @param {string} type
 *   The input value type.
 * @param {string} value
 *   The input value.
 *
 * @returns {Promise}
 *   A promise that contains the data and status in case of success and error
 * object in case of failure.
 */
window.auraBackend.updateLoyaltyCard = (action, type, value) => postAPIData('post/loyalty-club/update-loyalty-card', {
  action,
  type,
  value,
});

/*
 * Update User's AURA Status.
 *
 * @param {Object} inputData
 *   Input data object.
 *
 * @returns {Promise}
 *   Return success/failure response.
 */
window.auraBackend.updateUserAuraStatus = (inputData) => postAPIData('post/loyalty-club/apc-status-update', inputData);
