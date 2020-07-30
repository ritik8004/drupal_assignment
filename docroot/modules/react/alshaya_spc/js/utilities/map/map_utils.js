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
    mapping.locality = ['neighborhood', 'locality'];
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
 * When click on map.
 */
export const onMapClick = (e) => {
  // Dispatch event so that other can use this.
  const event = new CustomEvent('mapClicked', {
    bubbles: true,
    detail: {
      coords: () => e.latLng,
    },
  });
  document.dispatchEvent(event);
};

/**
 * When marker is dragged event.
 */
export const onMarkerDragEnd = (e) => {
  onMapClick(e);
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

  const marker = new window.google.maps.Marker({
    position,
    map,
    icon,
    draggable: true,
  });

  marker.addListener('dragend', onMarkerDragEnd);
  return marker;
};

/**
 * Create info window.
 */
export const createInfoWindow = (content) => new window.google.maps.InfoWindow({
  content,
});

/**
 * Convert array of google map address to key-value mapping based on
 * druapl config.
 *
 * @param {array} address
 */
function convertGmapAddress(addresses) {
  const googleFieldArray = Object.entries(mapAddressMap());

  const fullMapping = {};
  googleFieldArray.forEach(([key, googleFields]) => {
    fullMapping[key] = [...googleFields];
    addresses.forEach((address) => {
      address.address_components.forEach((gmapVal) => {
        const foundKey = fullMapping[key].filter((field) => gmapVal.types.includes(field));
        const matchingIndex = fullMapping[key].indexOf(foundKey[0]);
        if (foundKey.length > 0 && matchingIndex >= 0) {
          fullMapping[key][matchingIndex] = gmapVal.long_name;
        }
      });
    });
    fullMapping[key] = fullMapping[key].filter((field) => !googleFields.includes(field));
  });
  return fullMapping;
}

/**
 * Fill the address form based on geocode info.
 *
 * @param {array} address
 */
export const fillValueInAddressFromGeocode = (addresses) => {
  const gMapAddress = convertGmapAddress(addresses);

  Object.entries(drupalSettings.address_fields).forEach(
    ([key]) => {
      // Some handling for select list fields (areas/city).
      if ((key === 'administrative_area' || key === 'area_parent') || !gMapAddress[key]) return;
      if (gMapAddress[key] && gMapAddress[key].length === 0) return;
      const [val] = gMapAddress[key];
      // We will handle area/parent area separately.
      document.getElementById(key).value = val;
      document.getElementById(key).classList.add('focus');
    },
  );

  let areaParentValue = null;
  // If area parent available.
  if (drupalSettings.address_fields.area_parent !== undefined
    && gMapAddress.area_parent.length > 0) {
    gMapAddress.area_parent.some((area) => {
      areaParentValue = document.querySelector(
        `[data-list=areas-list][data-parent-label*="${area}" i]`,
      );
      return areaParentValue;
    });

    if (areaParentValue) {
      areaParentValue = {
        id: areaParentValue.getAttribute('data-parent-id'),
        label: areaParentValue.getAttribute('data-parent-label'),
      };
    }
    // Trigger event to update parent area field.
    const updateEvent = new CustomEvent('updateParentAreaOnMapSelect', {
      bubbles: true,
      detail: {
        data: () => (areaParentValue !== null ? areaParentValue : ''),
      },
    });
    document.dispatchEvent(updateEvent);
  }

  // If area field available.
  if (drupalSettings.address_fields.administrative_area !== undefined
    && gMapAddress.administrative_area.length > 0) {
    let areaVal = null;
    gMapAddress.administrative_area.some((area) => {
      areaVal = document.querySelector(`[data-list=areas-list][data-label*="${area}" i]`);
      return areaVal;
    });

    if (areaVal) {
      areaVal = {
        id: areaVal.getAttribute('data-id'),
        label: areaVal.getAttribute('data-label'),
      };

      // If area parent field is available.
      if (areaVal.id !== undefined
        && drupalSettings.address_fields.area_parent !== undefined) {
        let matchedParent = false;
        // If parent field has value.
        if (areaParentValue !== null
          && areaParentValue.id !== undefined) {
          // If parent area field is available and it has value.
          // Checking here if the area value we get has same
          // parent as the parent we get for the area_parent.
          const parentValue = getAreaParentId(true, areaVal.label);
          // If there are parent for given area.
          if (parentValue) {
            matchedParent = parentValue.some((parentArea) => parentArea.id === areaParentValue.id);
          }
        }

        // If matched parent not available.
        areaVal = (matchedParent === false) ? null : areaVal;
      }
    }

    // Trigger event to update area field.
    const updateArea = new CustomEvent('updateAreaOnMapSelect', {
      bubbles: true,
      detail: {
        data: () => (areaVal !== null ? areaVal : ''),
      },
    });
    document.dispatchEvent(updateArea);
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
                && addressItem.short_name === drupalSettings.country_code),
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
