/**
 * Checks if the topup quote is expired or not.
 *
 * @returns {integer}
 *   Returns true if topup quote is expired else false.
 */
export const getDistanceBetween = (location1, location2) => {
  // The math module contains a function
  // named toRadians which converts from
  // degrees to radians.

  const lon1 = (parseInt((location1.lng), 10) * Math.PI) / 180;
  const lon2 = (parseInt((location2.lng), 10) * Math.PI) / 180;
  const lat1 = (parseInt((location1.lat), 10) * Math.PI) / 180;
  const lat2 = (parseInt((location1.lat), 10) * Math.PI) / 180;

  // Haversine formula
  const dlon = lon2 - lon1;
  const dlat = lat2 - lat1;
  const a = (Math.sin(dlat / 2) ** 2)
    + Math.cos(lat1) * Math.cos(lat2)
    * (Math.sin(dlon / 2) ** 2);

  const c = 2 * Math.asin(Math.sqrt(a));
  // Radius of earth in kilometers.
  const r = 6371;
  // calculate the result
  return (c * r);
};

/**
 * Checks if the topup quote is expired or not.
 *
 * @returns {array}
 *   Returns true if topup quote is expired else false.
 */
export const nearByStores = (stores, currentLocation) => {
  const nearbyStores = stores.filter((store) => {
    const otherLocation = { lat: +store.latitude, lng: +store.longitude };
    const distance = getDistanceBetween(currentLocation, otherLocation);
    const proximity = drupalSettings.storeLabels.search_proximity_radius || 5;
    return (distance < proximity) ? store : null;
  });
  return nearbyStores;
};
