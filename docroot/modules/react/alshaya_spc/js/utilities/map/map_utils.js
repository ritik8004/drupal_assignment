import {
  getAreaParentId,
} from '../address_util';
import {
  getDefaultMapCenter,
} from '../checkout_util';

/**
 * Prepare mapping of the google geocode.
 */
export const mapAddressMap = () => {
  let mapping = [];
  // If mapping is available in settings, use that.
  if (window.drupalSettings.google_field_mapping !== null) {
    mapping = window.drupalSettings.google_field_mapping;
  } else {
    // For street.
    mapping.address_line1 = ['route', 'street_number'];
    mapping.address_line2 = ['park', 'point_of_interest', 'establishment', 'premise'];
    // For area.
    mapping.administrative_area = ['sublocality_level_1', 'administrative_area_level_1', 'locality'];
    // For area parent.
    mapping.area_parent = ['administrative_area_level_1'];
    // For locality.
    mapping.locality = ['locality'];
  }

  return mapping;
};

/**
 * Get the map from window object.
 *
 * This is stored in window object in <GoogleMap:componentDidMount>
 */
export const getMap = () => window.spcMap;

/**
 * Wrapper to get default map coords.
 */
export const getDefaultMapCoords = () => getDefaultMapCenter();

/**
 * Get zoom value for HD map.
 */
export const getHDMapZoom = () => 18;

/**
 * Get the markers available on the map.
 *
 * See <GoogleMap> for more details.
 */
export const getMarkers = () => window.spcMarkers;

/**
 * Removes all markers from map.
 */
export const removeAllMarkersFromMap = () => {
  // First clear all existing marker on map.
  for (let i = 0; i < window.spcMarkers.length; i++) {
    window.spcMarkers[i].setMap(null);
  }
  window.spcMarkers = [];
};

/**
 * Create a marker.
 *
 * @param {*} position
 * @param {*} map
 */
export const createMarker = (position, map) => {
  let icon = '';
  if (drupalSettings.map.map_marker !== undefined
    && drupalSettings.map.map_marker !== null
    && drupalSettings.map.map_marker.active !== undefined) {
    icon = drupalSettings.map.map_marker.active;
  }

  return new window.google.maps.Marker({
    position,
    map,
    icon,
  });
};

/**
   * Create info window.
   */
export const createInfoWindow = (content) => new window.google.maps.InfoWindow({
  content,
});

/**
 * Get value from google geocode for address form.
 *
 * @param {*} addressArray
 * @param {*} key
 */
export const getAddressFieldVal = (addressArray, key) => {
  const fieldVal = '';
  const fieldData = [];
  // Get the mapping.
  const addressMap = mapAddressMap();
  for (let i = 0; i < addressArray.length; i++) {
    if (addressArray[i].types[0]) {
      // If mapping set.
      if (addressMap[key] !== undefined) {
        for (let k = 0; k < addressMap[key].length; k++) {
          const type = addressMap[key][k];
          if (addressArray[i].types.indexOf(type) !== -1) {
            const data = {
              type,
              val: addressArray[i].long_name,
            };
            fieldData.push(data);
          }
        }
      }
    }
  }

  const listData = [];
  if (fieldData.length > 0) {
    for (let i = 0; i < addressMap[key].length; i++) {
      for (let j = 0; j < fieldData.length; j++) {
        if (fieldData[j].type === addressMap[key][i]) {
          // If any list field (are/city), we return array of all values.
          if (key === 'area_parent' || key === 'administrative_area') {
            listData.push(fieldData[j].val);
          } else {
            // For other plain fields.
            return fieldData[j].val;
          }
        }
      }
    }

    if (listData.length > 0) {
      return listData;
    }
  }

  return fieldVal;
};

/**
 * Deduce area name from available areas based from google.
 *
 * @param {*} area
 */
export const deduceAreaVal = (area, field) => {
  const areas = document.querySelectorAll('[data-list=areas-list]');
  if (areas.length > 0) {
    for (let j = 0; j < area.length; j++) {
      for (let i = 0; i < areas.length; i++) {
        const labelAttribute = field === 'area_parent' ? 'data-parent-label' : 'data-label';
        const areaLable = areas[i].getAttribute(labelAttribute);
        // If it matches with some value.
        const areaVal = area[j].trim().toLowerCase();
        if (areaLable.toLowerCase().indexOf(areaVal) !== -1
          || areaVal.indexOf(areaLable.toLowerCase()) !== -1) {
          const idAttribute = field === 'area_parent' ? 'data-parent-id' : 'data-id';
          return {
            id: areas[i].getAttribute(idAttribute),
            label: areaLable,
          };
        }
      }
    }
  }
  return null;
};

/**
 * Fill the address form based on geocode info.
 *
 * @param {*} address
 */
export const fillValueInAddressFromGeocode = (address) => {
  Object.entries(drupalSettings.address_fields).forEach(
    ([key]) => {
      // Some handling for select list fields (areas/city).
      if ((key !== 'administrative_area' && key !== 'area_parent')) {
        // We will handle area/parent area separately.
        const val = getAddressFieldVal(address, key).trim();
        document.getElementById(key).value = val;
        document.getElementById(key).classList.add('focus');
      }
    },
  );

  let areaParentValue = null;
  // If area parent available.
  if (drupalSettings.address_fields.area_parent !== undefined) {
    let areaVal = [];
    const val = getAddressFieldVal(address, 'area_parent');
    // If not empty.
    if (val.length > 0) {
      areaVal = deduceAreaVal(val, 'area_parent');
      if (areaVal !== null) {
        areaParentValue = areaVal;
        // Trigger event.
        const updateEvent = new CustomEvent('updateParentAreaOnMapSelect', {
          bubbles: true,
          detail: {
            data: () => areaVal,
          },
        });
        document.dispatchEvent(updateEvent);
      }
    }
  }

  // If area field available.
  if (drupalSettings.address_fields.administrative_area !== undefined) {
    const val = getAddressFieldVal(address, 'administrative_area');
    // If not empty.
    if (val.length > 0) {
      // Deduce value from the available area in drupal.
      const areaVal = deduceAreaVal(val, 'administrative_area');
      // If we have area value matching.
      if (areaVal !== null && areaVal.id !== undefined) {
        let triggerAreaUpdateEvent = false;
        // If area parent field not available, then trigger event.
        if (drupalSettings.address_fields.area_parent === undefined) {
          triggerAreaUpdateEvent = true;
        } else if (areaParentValue !== null
          && areaParentValue.id !== undefined) {
          // If parent area field is available and it has value.
          // Checking here if the area value we get has same
          // parent as the parent we get for the area_parent.
          const parentValue = getAreaParentId(true, areaVal.label);
          // If there are parent for given area.
          if (parentValue !== null) {
            for (let i = 0; i < parentValue.length; i++) {
              // If matches with a parent.
              if (parentValue[i].id === areaParentValue.id) {
                triggerAreaUpdateEvent = true;
                break;
              }
            }
          }
        }

        if (triggerAreaUpdateEvent) {
          // Trigger event.
          const updateArea = new CustomEvent('updateAreaOnMapSelect', {
            bubbles: true,
            detail: {
              data: () => areaVal,
            },
          });
          document.dispatchEvent(updateArea);
        }
      }
    }
  }
};

/**
 * Geocode address on the map.
 */
export const geocodeAddressToLatLng = () => {
  let address = [];
  Object.entries(window.drupalSettings.address_fields).forEach(
    ([key]) => {
      const fieldVal = document.getElementById(key).value;
      if (fieldVal.trim().length > 0) {
        if (key === 'area_parent') {
          const city = document.getElementById('spc-area-select-selected-city').innerText;
          address.push(city.trim());
        } else if (key === 'administrative_area') {
          const area = document.getElementById('spc-area-select-selected').innerText;
          address.push(area.trim());
        } else {
          address.push(fieldVal.trim());
        }
      }
    },
  );

  address = address.join(', ');
  // If we have address available.
  if (address.length > 0) {
    const geocoder = new google.maps.Geocoder();
    geocoder.geocode({
      componentRestrictions: {
        country: window.drupalSettings.country_code,
      },
      address,
    }, (results, status) => {
      if (status === 'OK') {
        // Get the map and re-center it.
        const map = getMap();
        map.setCenter(results[0].geometry.location);
        map.setZoom(getHDMapZoom());
        // Remove any existing markers on map and add new marker.
        removeAllMarkersFromMap();
        const marker = createMarker(results[0].geometry.location, map);
        const markerArray = [];
        markerArray.push(marker);
        window.spcMarkers = markerArray;
      }
    });
  }
};
