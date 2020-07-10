const getDefaultMapCenter = () => {
  if (typeof drupalSettings.alshaya_appointment.store_finder !== 'undefined' && ({}).hasOwnProperty.call(drupalSettings.alshaya_appointment.store_finder, 'latitude') && ({}).hasOwnProperty.call(drupalSettings.alshaya_appointment.store_finder, 'longitude')) {
    const { latitude: lat, longitude: lng } = drupalSettings.alshaya_appointment.store_finder;
    return { lat, lng };
  }
  return {};
};
export default getDefaultMapCenter;