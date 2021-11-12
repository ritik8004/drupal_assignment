import logger from '../../../../alshaya_spc/js/utilities/logger';
import { hasValue } from '../../../../js/utilities/conditionsUtility';
import auraErrorCodes from './error';
import { getErrorResponse } from './utility';

/**
 * Stores the regex for valid email address.
 */
const emailRegex = new RegExp('/^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$');

/**
 * Validate input data based on type.
 *
 * @returns {Array}
 *   Error/empty array.
 */
const validateInput = (type, value) => {
  if (type === 'email') {
    if (!hasValue(value) || !emailRegex.test(value.toLowerCase())) {
      logger.error('Email is missing/invalid. Data: @data', {
        '@data': value,
      });
      return getErrorResponse(auraErrorCodes.EMPTY_EMAIL, auraErrorCodes.INVALID_EMAIL);
    }
    return [];
  }

  if (type === 'cardNumber' || type === 'apcNumber') {
    if (!hasValue(value) || value.match(/^\d+$/, value) === null) {
      logger.error('Card number is missing/invalid. Data: @data', {
        '@data': value,
      });
      return getErrorResponse(auraErrorCodes.EMPTY_CARD, auraErrorCodes.INVALID_CARDNUMBER);
    }
    return [];
  }

  if (type === 'mobile' || type === 'phone') {
    const filteredValue = value.replace('+', '');
    if (!hasValue(value) || filteredValue.match(/^\d+$/, filteredValue) === null) {
      logger.error('Mobile number is missing/invalid. Data: @data', {
        '@data': value,
      });
      return getErrorResponse(auraErrorCodes.EMPTY_MOBILE, auraErrorCodes.INVALID_MOBILE);
    }
    return [];
  }

  return [];
};

export default validateInput;
