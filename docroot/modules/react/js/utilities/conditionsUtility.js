const hasValue = (value) => {
  if (typeof value === 'undefined') {
    return false;
  }

  if (value === null) {
    return false;
  }

  if (Object.prototype.hasOwnProperty.call(value, 'length') && value.length === 0) {
    return false;
  }

  if (value.constructor === Object && Object.keys(value).length === 0) {
    return false;
  }

  return Boolean(value);
};

/**
 * Helper function to check if value is string.
 */
const isString = (value) => {
  if (typeof value !== 'undefined' && typeof value.valueOf() === 'string') {
    return true;
  }
  return false;
};


/**
 * Helper function to check if value is number.
 */
const isNumber = (value) => {
  if (typeof value !== 'undefined' && typeof value.valueOf() === 'number' && !(value instanceof Date)) {
    return true;
  }
  return false;
};

/**
 * Helper function to check if value is Boolean.
 */
const isBoolean = (value) => {
  if (typeof value !== 'undefined' && typeof value.valueOf() === 'boolean') {
    return true;
  }
  return false;
};

/**
 * Helper function to check if value is Array.
 */
const isArray = (value) => {
  if (Array.isArray(value)) {
    return true;
  }
  return false;
};

/**
 * Helper function to check if value is Object.
 */
const isObject = (value) => {
  const type = typeof value;
  return ((type === 'object' || type === 'function') && !Array.isArray(value));
};

export {
  hasValue,
  isString,
  isNumber,
  isBoolean,
  isArray,
  isObject,
};
