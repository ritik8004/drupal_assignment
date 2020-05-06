import React from 'react';

import {
  createMarker,
  geocodeAddressToLatLng,
  getDefaultMapCoords,
  removeAllMarkersFromMap,
  getMap,
  getHDMapZoom,
  onMapClick,
} from './map_utils';
import isRTL from '../rtl';
import getStringMessage from '../strings';

export default class GoogleMap extends React.Component {
  constructor(props) {
    super(props);
    this.googleMapRef = React.createRef();
    // Global map object.
    this.googleMap = null;
    // Global for list of markers on map.
    this.markers = [];
    // Global object for autocomplete.
    this.autocomplete = null;
  }

  componentDidMount() {
    const controlPosition = isRTL() === true
      ? window.google.maps.ControlPosition.RIGHT_BOTTOM
      : window.google.maps.ControlPosition.LEFT_BOTTOM;

    const data = {};

    // Create map object. Initial map center coordinates
    // can be provided from the caller in props.
    this.googleMap = this.createGoogleMap(data, controlPosition);
    const mapCenter = this.googleMap.getCenter();
    this.panMapToGivenCoords({
      lat: mapCenter.lat(),
      lng: mapCenter.lng(),
    });

    // Storing in global so that can be accessed byt parent and others.
    window.spcMap = this.googleMap;

    // Add My location button.
    this.addMyLocationButton(window.spcMap, controlPosition);

    // This can be passed from props if click on
    // map is allowed or not.
    const mapClickable = true;
    if (mapClickable) {
      this.googleMap.addListener('click', onMapClick);
    }

    // Storing all markers in global so that can be accessed from anywhere.
    window.spcMarkers = this.markers;

    // For autocomplete textfield.
    this.autocomplete = new window.google.maps.places.Autocomplete(this.autocompleteTextField(), {
      types: [],
      componentRestrictions: { country: window.drupalSettings.country_code },
    });
    this.autocomplete.addListener('place_changed', this.placesAutocompleteHandler);
    window.spcAutoComplete = this.autocomplete;
  }

  /**
   * Get current location coordinates.
   */
  setCurrentLocationCoords = () => {
    // If location access is enabled by user.
    if (navigator && navigator.geolocation) {
      navigator.geolocation.getCurrentPosition((pos) => {
        const currentCoords = {
          lat: pos.coords.latitude,
          lng: pos.coords.longitude,
        };

        const geocoder = new window.google.maps.Geocoder();
        geocoder.geocode({
          location: currentCoords,
        },
        (results, status) => {
          if (status === 'OK') {
            if (results[0]) {
              // Use this address info.
              const address = results[0].address_components;

              // Flag to determine if user country same as site.
              let userCountrySame = false;
              // Checking if user current location belongs to same
              // country or not by location coords geocode.
              for (let i = 0; i < address.length; i++) {
                if (address[i].types.indexOf('country') !== -1
                    && address[i].short_name === drupalSettings.country_code) {
                  userCountrySame = true;
                  break;
                }
              }

              // If user and site country not same, don;t process.
              if (!userCountrySame) {
                // @TODO: Add some indication to user.
                return;
              }

              // Remove all markers from the map.
              removeAllMarkersFromMap();
              // Pan the map to location.
              const marker = createMarker(currentCoords, getMap());
              getMap().panTo(marker.getPosition());
              getMap().setZoom(getHDMapZoom());
              window.spcMarkers.push(marker);
            }
          }
        });
      });
    }

    return {};
  }

  /**
   * Pan map to given location with coords and marker.
   */
  panMapToGivenCoords = (coords) => {
    this.removeAllMarkersFromMap();
    // Keep only currently selected marker.
    const marker = createMarker(coords, this.googleMap);
    this.googleMap.panTo(marker.getPosition());
    this.markers.push(marker);
    window.spcMarkers = this.markers;
  }

  /**
   * Autocomplete handler for the places list.
   */
  placesAutocompleteHandler = () => {
    const place = window.spcAutoComplete.getPlace();
    const event = new CustomEvent('mapClicked', {
      bubbles: true,
      detail: {
        coords: () => place.geometry.location,
      },
    });
    document.dispatchEvent(event);
  }

  /**
   * Removes all markers from map.
   */
  removeAllMarkersFromMap = () => {
    // First clear all existing marker on map.
    for (let i = 0; i < this.markers.length; i++) {
      this.markers[i].setMap(null);
    }
    this.markers = [];
    window.spcMarkers = this.markers;
  }

  /**
   * Get google map div.
   */
  googleMapDiv = () => document.getElementById('google-map')

  /**
   * Get autocomplete text field.
   */
  autocompleteTextField = () => document.getElementById('searchTextField');

  /**
   * Adds my location button on the map.
   *
   * @param map
   * The google map object.
   *
   * @param controlPosition
   * The position of controls on Google Map.
   */
  addMyLocationButton = (map, controlPosition) => {
    // We create a div > button > div markup for the custom button.
    // The wrapper div.
    const controlDiv = document.createElement('div');

    // The button element.
    const firstChild = document.createElement('button');
    firstChild.style.backgroundColor = '#fff';
    firstChild.style.border = 'none';
    firstChild.style.outline = 'none';
    firstChild.style.width = '28px';
    firstChild.style.height = '28px';
    firstChild.style.borderRadius = '2px';
    firstChild.style.boxShadow = '0 1px 4px rgba(0,0,0,0.3)';
    firstChild.style.cursor = 'pointer';
    firstChild.style.padding = '0px';
    firstChild.title = Drupal.t('Your Location');
    if (isRTL()) {
      firstChild.style.marginRight = '15px';
    } else {
      firstChild.style.marginLeft = '15px';
    }

    controlDiv.appendChild(firstChild);

    // The child div.
    const secondChild = document.createElement('div');
    secondChild.style.margin = '5px';
    secondChild.style.width = '18px';
    secondChild.style.height = '18px';
    secondChild.style.backgroundImage = 'url(/themes/custom/transac/alshaya_white_label/imgs/icons/mylocation-sprite-1x.png)';
    secondChild.style.backgroundSize = '180px 18px';
    secondChild.style.backgroundPosition = '0px 0px';
    secondChild.style.backgroundRepeat = 'no-repeat';
    secondChild.id = 'you_location_img';
    firstChild.appendChild(secondChild);

    const locationImg = document.getElementById('you_location_img');

    // Add dragend event listener on map to fade the button once map is not
    // showing the current location.
    window.google.maps.event.addListener(map, 'dragend', () => {
      if (typeof (locationImg) !== 'undefined' && locationImg !== null) {
        document.getElementById('you_location_img').style.backgroundPosition = '0px 0px';
      }
    });

    // Add click event listener on button to get user's location from
    // browser.
    firstChild.addEventListener('click', () => {
      const animationInterval = setInterval(() => {
        let imgX = '0';
        imgX = imgX === '-18' ? '0' : '-18';
        if (typeof (locationImg) !== 'undefined' && locationImg !== null) {
          document.getElementById('you_location_img').style.backgroundPosition = `${imgX}px 0px`;
        }
      }, 500);

      // Geolocation.
      if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition((position) => {
          const latlng = new window.google.maps.LatLng(
            position.coords.latitude,
            position.coords.longitude,
          );

          // Center map on user's location.
          map.setCenter(latlng);
          // Stop animation.
          clearInterval(animationInterval);
          // Change icon color.
          if (typeof (locationImg) !== 'undefined' && locationImg !== null) {
            document.getElementById('you_location_img').style.backgroundPosition = '-144px 0px';
          }
        });
      } else {
        // Stop animation.
        clearInterval(animationInterval);
        // Change icon color to disabled.
        if (typeof (locationImg) !== 'undefined' && locationImg !== null) {
          document.getElementById('you_location_img').style.backgroundPosition = '0px 0px';
        }
      }
    });

    // Add to map.
    controlDiv.index = 1;
    map.controls[controlPosition].push(controlDiv);
  };

  /**
   * Set coords on map by country.
   */
  setCountryCoords = () => {
    // Get the coords of the country.
    const geocoder = new google.maps.Geocoder();
    geocoder.geocode({
      address: drupalSettings.country_code,
    }, (results, status) => {
      if (status === google.maps.GeocoderStatus.OK) {
        // this.googleMap.setCenter(results[0].geometry.location);
        window.spcMap.setCenter(results[0].geometry.location);
      }
    });
  };

  /**
   * Create google map.
   */
  createGoogleMap = (centerPosition, controlPosition) => {
    let centerPos = centerPosition;
    const { isEditAddress } = this.props;
    // If corrds not available, try country coords.
    if (centerPos.lat === undefined) {
      // If address is being edited, get coords from
      // address detail.
      if (isEditAddress) {
        geocodeAddressToLatLng();
      }

      // As coords are required for initialize google
      // map object and if user has not allowed location
      // share, we first center the map to middle-east region
      // and then re-center map on the country center.
      centerPos = getDefaultMapCoords();
    }

    return new window.google.maps.Map(this.googleMapDiv(), {
      zoom: 7,
      center: centerPos,
      disableDefaultUI: false,
      mapTypeControl: false,
      streetViewControl: false,
      fullscreenControl: false,
      clickableIcons: false,
      zoomControlOptions: {
        position: controlPosition,
      },
    });
  }

  render() {
    return (
      <div className="spc-google-map">
        <div className="spc-location-g-map-search form-type-textfield">
          <input placeholder={getStringMessage('map_enter_location')} ref={(ref) => { this.autocomplete = ref; }} id="searchTextField" type="text" />
        </div>
        <div id="google-map" ref={this.googleMapRef} style={{ width: '100%', height: '100%' }} />
      </div>
    );
  }
}
