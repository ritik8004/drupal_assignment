/**
 * Prepare mapping of the google geocode.
 */
export const mapAddressMap = function () {
  let mapping = [];
  mapping['street'] = ['route', 'street_number'];
  mapping['address_block_segment'] = ['park', 'point_of_interest', 'establishment', 'premise'];
  mapping['area'] = ['sublocality_level_1', 'administrative_area_level_1'];
  return mapping;
}

/**
 * Get the map from window object.
 *
 * This is stored in window object in <GoogleMap:componentDidMount>
 */
export const getMap = function () {
  return window.spcMap;
}

/**
 * Get the markers available on the map.
 *
 * See <GoogleMap> for more details.
 */
export const getMarkers = function () {
  return window.spcMarkers;
}

/**
 * Removes all markers from map.
 */
export const removeAllMarkersFromMap = function () {
  // First clear all existing marker on map.
  for (var i = 0; i < window.spcMarkers.length; i++) {
    window.spcMarkers[i].setMap(null);
  }
  window.spcMarkers = [];
}

/**
 * Create a marker.
 *
 * @param {*} position
 * @param {*} map
 */
export const createMarker = function (position, map) {
  return new window.google.maps.Marker({
    position: position,
    map: map,
    icon: '' // This can be later dynamic based on HD or CnC.
  })
}

/**
   * Create info window.
   */
export const createInfoWindow = function (content) {
  return new window.google.maps.InfoWindow({
    content: content
  });
}

/**
 * Get value from google geocode for address form.
 *
 * @param {*} addressArray
 * @param {*} key
 */
export const getAddressFieldVal = function (addressArray, key) {
  let fieldVal = '';
  const addressMap = mapAddressMap();
  for (let i = 0; i < addressArray.length; i++) {
    if (addressArray[i].types[0]) {
      for (let j = 0; j < addressArray[i].types.length; j++) {
        // If mapping set.
        if (addressMap[key] !== undefined) {
          for (let k = 0; k < addressMap[key].length; k++) {
            if (addressArray[i].types[j] === addressMap[key][k]) {
              return addressArray[i].long_name;
            }
          }
        }
      }
    }
  }

  return fieldVal;
}

/**
 * Fill the address form based on geocode info.
 *
 * @param {*} address
 */
export const fillValueInAddressFromGeocode = function (address) {
  Object.entries(window.drupalSettings.address_fields).forEach(
    ([key, field]) => {
      let val = getAddressFieldVal(address, field['key']).trim();
      // Some handling for select list fields (areas/city).
      if (field.key === 'area'
        && val.length > 0) {
        let areaVal = deduceAreaVal(val, field.key);
        if (areaVal !== null) {
          var event = new CustomEvent('updateAreaOnMapSelect', {
            bubbles: true,
            detail: {
              data: () => areaVal
            }
          });
          document.dispatchEvent(event);
        }
      }
      else {
        document.getElementById(key).value = val;
      }
    }
  );
}

/**
 * Deduce area name from available areas based from google.
 *
 * @param {*} area
 */
export const deduceAreaVal = function (area) {
  let areas = document.querySelectorAll('[data-list=areas-list]');
  if (areas.length > 0) {
    for (let i = 0; i < areas.length; i++) {
      let areaLable = areas[i].getAttribute('data-label');
      // If it matches with some value.
      if (areaLable.indexOf(area) !== -1 ||
      area.indexOf(areaLable) !== -1) {
        return {
          id: areas[i].getAttribute('data-id'),
          label: areaLable
        }
      }
    }
  }

  return null;
}
