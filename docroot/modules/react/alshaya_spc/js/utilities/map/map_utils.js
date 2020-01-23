/**
  * Get geocode data from lat/long.
  *
  * @param location
  */
export const getGeoCodeFromLatLng = function (location) {
  let geocoder = new window.google.maps.Geocoder()
  geocoder.geocode({'location': location}, function(results, status) {
    if (status === 'OK') {
      if (results[0]) {
        // Use this address info.
        const address = results[0].address_components;
        return address;
      }
    }
  });
}

/**
  * Get the city.
  *
  * @param address
  * @return {string}
  */
export const getCity = function (address) {
  let city = '';
  for( let i = 0; i < addressArray.length; i++ ) {
    if ( addressArray[ i ].types[0] && 'administrative_area_level_2' === addressArray[ i ].types[0] ) {
      city = addressArray[ i ].long_name;
      return city;
    }
  }
}

/**
  * Get the area.
  *
  * @param address
  * @return {string}
  */
export const getArea = function (address) {
  let area = '';
  for( let i = 0; i < addressArray.length; i++ ) {
    if ( addressArray[ i ].types[0]  ) {
      for ( let j = 0; j < addressArray[ i ].types.length; j++ ) {
        if ( 'sublocality_level_1' === addressArray[ i ].types[j] || 'locality' === addressArray[ i ].types[j] ) {
          area = addressArray[ i ].long_name;
          return area;
        }
      }
    }
  }
}

/**
  * Get the state.
  *
  * @param address
  * @return {string}
  */
 export const getState = function (address) {
  let state = '';
  for( let i = 0; i < addressArray.length; i++ ) {
    for( let i = 0; i < addressArray.length; i++ ) {
      if ( addressArray[ i ].types[0] && 'administrative_area_level_1' === addressArray[ i ].types[0] ) {
        state = addressArray[ i ].long_name;
        return state;
      }
    }
  }
}
