import logger from '../../../../js/utilities/logger';
import { hasValue } from '../../../../js/utilities/conditionsUtility';
import { getErrorResponse } from '../../../../js/utilities/error';
import auraErrorCodes from '../utility/error';

/**
 * Validate input data based on type.
 *
 * @param {string} type
 *   Input field type.
 * @param {string} value
 *   Input field value.
 *
 * @returns {object}
 *   Error/empty array.
 */
const validateInput = (type, value) => {
  if (type === 'email') {
    if (!hasValue(value) || !(/^\S+@\S+\.\S+$/).test(value.toLowerCase())) {
      logger.error('Email is missing/invalid. Data: @data', {
        '@data': value,
      });
      return getErrorResponse(auraErrorCodes.EMPTY_EMAIL, auraErrorCodes.INVALID_EMAIL, true);
    }
    return [];
  }

  if (type === 'cardNumber' || type === 'apcNumber') {
    if (!hasValue(value) || value.match(/^\d+$/, value) === null) {
      logger.error('Card number is missing/invalid. Data: @data', {
        '@data': value,
      });
      return getErrorResponse(auraErrorCodes.EMPTY_CARD, auraErrorCodes.INVALID_CARDNUMBER, true);
    }
    return [];
  }

  if (type === 'mobile' || type === 'phone') {
    const filteredValue = value.replace('+', '');
    if (!hasValue(value) || filteredValue.match(/^\d+$/, filteredValue) === null) {
      logger.error('Mobile number is missing/invalid. Data: @data', {
        '@data': value,
      });
      return getErrorResponse(auraErrorCodes.EMPTY_MOBILE, auraErrorCodes.INVALID_MOBILE, true);
    }
    return [];
  }

  return [];
};

export default validateInput;
