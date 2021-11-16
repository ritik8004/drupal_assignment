/**
 * Returns the error in a specific format.
 *
 * @param {string} message
 *   The processed error message.
 * @param {string} code
 *   The error code.
 *
 * @returns {Object}
 *   The object containing the error data.
 */
const getErrorResponse = (message, code = '-') => ({
  error: true,
  error_message: message,
  error_code: code,
});

export default getErrorResponse;
