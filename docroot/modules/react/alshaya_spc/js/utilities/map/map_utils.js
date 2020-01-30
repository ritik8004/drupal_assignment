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
  * Get the city.
  *
  * @param addressArray
  * @return {string}
  */
export const getCity = function (addressArray) {
  let city = '';
  for( let i = 0; i < addressArray.length; i++ ) {
    if ( addressArray[ i ].types[0] && 'administrative_area_level_2' === addressArray[ i ].types[0] ) {
      city = addressArray[ i ].long_name;
    }
  }

  return city;
}

/**
  * Get the area.
  *
  * @param addressArray
  * @return {string}
  */
export const getArea = function (addressArray) {
  let area = '';
  for( let i = 0; i < addressArray.length; i++ ) {
    if ( addressArray[ i ].types[0]  ) {
      for ( let j = 0; j < addressArray[ i ].types.length; j++ ) {
        if ( 'sublocality_level_1' === addressArray[ i ].types[j] || 'locality' === addressArray[ i ].types[j] ) {
          area = addressArray[ i ].long_name;
        }
      }
    }
  }

  return area;
}

/**
  * Get the state.
  *
  * @param addressArray
  * @return {string}
  */
 export const getState = function (addressArray) {
  let state = '';
  for( let i = 0; i < addressArray.length; i++ ) {
    for( let i = 0; i < addressArray.length; i++ ) {
      if ( addressArray[ i ].types[0] && 'administrative_area_level_1' === addressArray[ i ].types[0] ) {
        state = addressArray[ i ].long_name;
      }
    }
  }

  return state;
}

/**
  * Get the block.
  *
  * @param addressArray
  * @return {string}
  */
 export const getBlock = function (addressArray) {
  let block = '';
  for( let i = 0; i < addressArray.length; i++ ) {
    if ( addressArray[ i ].types[0]  ) {
      for ( let j = 0; j < addressArray[ i ].types.length; j++ ) {
        if ( 'sublocality_level_2' === addressArray[ i ].types[j] ) {
          block = addressArray[ i ].long_name;
        }
      }
    }
  }

  return block;
}
