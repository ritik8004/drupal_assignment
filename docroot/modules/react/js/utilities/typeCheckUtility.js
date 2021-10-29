/**
 * Helper function to check if value is defined.
 */
function hasValue(value) {
  if (typeof value === 'undefined' || value === null) {
    return false;
  }
  return true;
}

/**
 * Helper function to check if value is string.
 */
const isString = (value) => {
  if (hasValue(value) && typeof value.valueOf() === 'string') {
    return true;
  }
  return false;
};


/**
 * Helper function to check if value is number.
 */
const isNumber = (value) => {
  if (hasValue(value) && typeof value.valueOf() === 'number') {
    return true;
  }
  return false;
};

/**
 * Helper function to check if value is Boolean.
 */
const isBoolean = (value) => {
  if (hasValue(value) && typeof value === 'boolean') {
    return true;
  }
  return false;
};

/**
 * Helper function to check if value is Array.
 */
const isArray = (value) => {
  if (hasValue(value) && Array.isArray(value)) {
    return true;
  }
  return false;
};

/**
 * Helper function to check if value is Object.
 */
const isObject = (value) => {
  const type = typeof value;
  return hasValue(value) && (type === 'object' || type === 'function');
};

export {
  isString,
  isNumber,
  isArray,
  isObject,
  isBoolean,
};
