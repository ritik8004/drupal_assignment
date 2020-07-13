/**
 * Helper function to get input value based on input type.
 */
function getInputValue(e) {
  let { value } = e.target;

  switch (e.target.type) {
    case 'checkbox':
      value = e.target.checked;
      break;
    case 'select-one':
      value = { id: e.target.value, name: e.target.options[e.target.selectedIndex].text };
      break;
    case 'radio':
      value = e.target.value;
      break;
    default:
      break;
  }
  return value;
}

function getLocationAccess() {
  // If location access is enabled by user.
  if (navigator && navigator.geolocation) {
    return new Promise(
      (resolve, reject) => navigator.geolocation.getCurrentPosition(resolve, reject),
    );
  }

  return new Promise(
    (resolve) => resolve({}),
  );
}

function addressCleanup(address) {
  let cleanAddress = '';
  if (address) {
    Object.entries(address).forEach(([i, value]) => {
      // Removing not available string (N/A) and countryCode from address.
      if (value.trim() && value !== '(N/A)' && i !== 'countryCode') {
        cleanAddress += (i !== 'address1') ? `, ${value}` : value;
      }
    });
  }

  return cleanAddress;
}

function getDateFormat() {
  const format = 'YYYY-MM-DD';
  return format;
}

function getDateFormattext() {
  const format = 'dddd DD MMMM';
  return format;
}

export {
  getInputValue,
  getLocationAccess,
  addressCleanup,
  getDateFormat,
  getDateFormattext,
};
