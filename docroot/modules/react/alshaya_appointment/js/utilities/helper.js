
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

export {
  getInputValue,
  getLocationAccess,
};
