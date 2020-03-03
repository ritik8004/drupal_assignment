/**
 * Prepare mapping of the google geocode.
 */
export const mapAddressMap = () => {
  let mapping = [];
  // For street.
  mapping['address_line1'] = ['route', 'street_number'];
  mapping['address_line2'] = ['park', 'point_of_interest', 'establishment', 'premise'];
  // For area.
  mapping['administrative_area'] = ['sublocality_level_1', 'administrative_area_level_1'];
  // For area parent.
  mapping['area_parent'] = ['administrative_area_level_1'];
  return mapping;
}

/**
 * Get the map from window object.
 *
 * This is stored in window object in <GoogleMap:componentDidMount>
 */
export const getMap = () => {
  return window.spcMap;
}

/**
 * Get the markers available on the map.
 *
 * See <GoogleMap> for more details.
 */
export const getMarkers = () => {
  return window.spcMarkers;
}

/**
 * Removes all markers from map.
 */
export const removeAllMarkersFromMap = () => {
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
export const createMarker = (position, map) => {
  return new window.google.maps.Marker({
    position: position,
    map: map,
    icon: '' // This can be later dynamic based on HD or CnC.
  })
}

/**
   * Create info window.
   */
export const createInfoWindow =  (content) => {
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
export const getAddressFieldVal = (addressArray, key) => {
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
export const fillValueInAddressFromGeocode = (address) => {
  Object.entries(window.drupalSettings.address_fields).forEach(
    ([key, field]) => {
      // Some handling for select list fields (areas/city).
      if ((key !== 'administrative_area' && key !== 'area_parent')) {
        // We will handle area/parent area separately.
        let val = getAddressFieldVal(address, key).trim();
        document.getElementById(key).value = val;
      }
    }
  );

  // If area parent available.
  if (window.drupalSettings.address_fields.area_parent !== undefined) {
    let areaVal = new Array();
    let val = getAddressFieldVal(address, 'area_parent').trim();
    // If not empty.
    if (val.length > 0) {
      areaVal = deduceAreaVal(val, 'area_parent');
      if (areaVal !== null) {
        // Trigger event.
        var event = new CustomEvent('updateAreaOnMapSelect', {
          bubbles: true,
          detail: {
            data: () => areaVal
          }
        });
        document.dispatchEvent(event);
      }
    }
  }

  // If area available and parent parent area is not available.
  if (window.drupalSettings.address_fields.administrative_area !== undefined
    && window.drupalSettings.address_fields.area_parent === undefined) {
    let areaVal = new Array();
    let val = getAddressFieldVal(address, 'administrative_area').trim();
    // If not empty.
    if (val.length > 0) {
      areaVal = deduceAreaVal(val, 'administrative_area');
      if (areaVal === null) {
        areaVal = new Array();
      }

      // Trigger event.
      var event = new CustomEvent('updateAreaOnMapSelect', {
        bubbles: true,
        detail: {
          data: () => areaVal
        }
      });
      document.dispatchEvent(event);
    }
  }
}

/**
 * Deduce area name from available areas based from google.
 *
 * @param {*} area
 */
export const deduceAreaVal = (area, field) => {
  let areas = document.querySelectorAll('[data-list=areas-list]');
  if (areas.length > 0) {
    for (let i = 0; i < areas.length; i++) {
      let labelAttribute = field === 'area_parent' ? 'data-parent-label' : 'data-label';
      let areaLable = areas[i].getAttribute(labelAttribute);
      // If it matches with some value.
      if (areaLable.toLowerCase().indexOf(area.toLowerCase()) !== -1 ||
        area.toLowerCase().indexOf(areaLable.toLocaleLowerCase()) !== -1) {
        let idAttribute = field === 'area_parent' ? 'data-parent-id' : 'data-id';
        return {
          id: areas[i].getAttribute(idAttribute),
          label: areaLable
        }
      }
    }
  }

  return null;
}
