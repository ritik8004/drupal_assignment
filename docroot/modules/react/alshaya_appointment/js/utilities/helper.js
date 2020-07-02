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

function convertKmToMile(value) {
  const realMiles = (value * 0.621371);
  const Miles = Math.floor(realMiles);

  return Miles;
}

function getDistanceBetweenCoords(storeList, coords) {
  const storeItems = google && storeList && Object.entries(storeList).map(([k, x]) => {
    const store = x;
    const distance = google.maps.geometry.spherical.computeDistanceBetween(
      new google.maps.LatLng(coords.lat, coords.lng),
      new google.maps.LatLng(x.geocoordinates.latitude, x.geocoordinates.longitude),
    );
    store.distanceInMiles = convertKmToMile(distance);
    return store;
  });

  return storeItems;
}

export {
  getInputValue,
  getLocationAccess,
  getDistanceBetweenCoords,
};
