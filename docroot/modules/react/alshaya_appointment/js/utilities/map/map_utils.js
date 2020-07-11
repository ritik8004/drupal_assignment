export const getDefaultMapCenter = () => {
  if (typeof drupalSettings.alshaya_appointment.store_finder !== 'undefined' && ({}).hasOwnProperty.call(drupalSettings.alshaya_appointment.store_finder, 'latitude') && ({}).hasOwnProperty.call(drupalSettings.alshaya_appointment.store_finder, 'longitude')) {
    const { latitude: lat, longitude: lng } = drupalSettings.alshaya_appointment.store_finder;
    return { lat, lng };
  }
  return {};
};

export const getUserLocation = (coords) => {
  const geocoder = new window.google.maps.Geocoder();
  // Flag to determine if user country same as site.
  let userCountrySame = false;
  let address = [];
  return new Promise((resolve, reject) => {
    geocoder.geocode(
      { location: coords },
      (results, status) => {
        if (status === 'OK') {
          if (results[0]) {
            // Use this address info.
            address = results[0].address_components;
            // Checking if user current location belongs to same
            // country or not by location coords geocode.
            userCountrySame = address.some(
              (addressItem) => (
                addressItem.types.indexOf('country') !== -1
                && addressItem.short_name === drupalSettings.alshaya_appointment.country_code),
            );
            resolve([userCountrySame, results]);
          }
        } else {
          reject(status);
        }
      },
    );
  });
};
