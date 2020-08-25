import getStringMessage from '../../../js/utilities/strings';

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

function getParam(param) {
  const { search } = window.location;
  const params = new URLSearchParams(search);
  return params.get(param);
}

function getArrayFromCompanionData(companionData) {
  if (companionData === undefined) {
    return [];
  }
  const companion = [];
  // Construct companion array,
  // as companionData has individual key for each field name, lastname, dob.
  for (let i = 1; i <= parseInt(Object.keys(companionData).length / 3, 10); i++) {
    const name = `bootscompanion${i}name`;
    const lastname = `bootscompanion${i}lastname`;
    const item = {
      label: `${getStringMessage('companion_label')} ${i}`,
      value: `${companionData[name]} ${companionData[lastname]}`,
    };
    companion.push(item);
  }
  return companion;
}

function getTimeFormat() {
  return 'hh:mm A';
}

function setMomentLocale(moment) {
  moment.defineLocale('ar-custom', {
    parentLocale: 'ar',
    preparse(string) {
      return string;
    },
    postformat(string) {
      return string;
    },
  });
}

function insertParam(paramKey, paramValue) {
  const key = encodeURIComponent(paramKey);
  const value = encodeURIComponent(paramValue);

  let s = document.location.search;
  const kvp = `${key}=${value}`;

  const r = new RegExp(`(&|\\?)${key}=[^]*`);

  s = s.replace(r, `$1${kvp}`);

  if (!RegExp.$1) { s += (s.length > 0 ? '&' : '?') + kvp; }

  // Load page with parameter.
  document.location.search = s;
}

export {
  getInputValue,
  getLocationAccess,
  addressCleanup,
  getDateFormat,
  getDateFormattext,
  getParam,
  getArrayFromCompanionData,
  getTimeFormat,
  setMomentLocale,
  insertParam,
};
