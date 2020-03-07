import React from 'react';

import {
  createMarker,
  geocodeAddressToLatLng
} from './map_utils';
import {isRTL} from '../rtl';

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
    let control_position = isRTL() === true ? window.google.maps.ControlPosition.RIGHT_BOTTOM : window.google.maps.ControlPosition.LEFT_BOTTOM;

    let data = {};
    // If adress is being edited, means don't need to
    // set current location.
    if (this.props.isEditAddress === false) {
      data = this.setCurrentLocationCoords();
    }

    // Create map object. Initial map center coordinates
    // can be provided from the caller in props.
    this.googleMap = this.createGoogleMap(data, control_position);

    // Storing in global so that can be accessed byt parent and others.
    window.spcMap = this.googleMap;

    // Add My location button.
    this.addMyLocationButton(window.spcMap, control_position);

    // This can be passed from props if click on
    // map is allowed or not.
    let mapClickable = true;
    if (mapClickable) {
      this.googleMap.addListener('click', this.onMapClick);
    }

    // Storing all markers in global so that can be accessed from anywhere.
    window.spcMarkers = this.markers;

    // For autocomplete textfield.
    this.autocomplete = new window.google.maps.places.Autocomplete(this.autocompleteTextField(), {
      types: [],
      componentRestrictions: {country: window.drupalSettings.country_code}
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
      navigator.geolocation.getCurrentPosition(pos => {
        let currentCoords = {
          'lat': pos.coords.latitude,
          'lng': pos.coords.longitude,
        };

        this.panMapToGivenCoords(currentCoords);
        return;
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
    var marker = createMarker(coords, this.googleMap);
    this.googleMap.panTo(marker.getPosition());
    this.markers.push(marker);
    window.spcMarkers = this.markers;
  }

  /**
   * Autocomplete handler for the places list.
   */
  placesAutocompleteHandler = () => {
    const place = window.spcAutoComplete.getPlace();
    let event = new CustomEvent('mapClicked', {
      bubbles: true,
      detail: {
        coords: () => place.geometry.location
      }
    });
    document.dispatchEvent(event);
  }

  /**
   * When click on map.
   */
  onMapClick = (e) => {
    // Dispatch event so that other can use this.
    let event = new CustomEvent('mapClicked', {
      bubbles: true,
      detail: {
        coords: () => e.latLng
      }
    });
    document.dispatchEvent(event);
  }

  /**
   * Removes all markers from map.
   */
  removeAllMarkersFromMap = () => {
    // First clear all existing marker on map.
    for (var i = 0; i < this.markers.length; i++) {
      this.markers[i].setMap(null);
    }
    this.markers = [];
    window.spcMarkers = this.markers;
  }

  /**
   * Get google map div.
   */
  googleMapDiv = () => {
    return document.getElementById('google-map');
  }

  /**
   * Get autocomplete text field.
   */
  autocompleteTextField = () => {
    return document.getElementById('searchTextField');
  };

  /**
   * Adds my location button on the map.
   *
   * @param map
   * The google map object.
   *
   * @param control_position
   * The position of controls on Google Map.
   */
  addMyLocationButton = (map, control_position) => {
    // We create a div > button > div markup for the custom button.
    // The wrapper div.
    var controlDiv = document.createElement('div');

    // The button element.
    var firstChild = document.createElement('button');
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
    }
    else {
      firstChild.style.marginLeft = '15px';
    }

    controlDiv.appendChild(firstChild);

    // The child div.
    var secondChild = document.createElement('div');
    secondChild.style.margin = '5px';
    secondChild.style.width = '18px';
    secondChild.style.height = '18px';
    secondChild.style.backgroundImage = 'url(/themes/custom/transac/alshaya_white_label/imgs/icons/mylocation-sprite-1x.png)';
    secondChild.style.backgroundSize = '180px 18px';
    secondChild.style.backgroundPosition = '0px 0px';
    secondChild.style.backgroundRepeat = 'no-repeat';
    secondChild.id = 'you_location_img';
    firstChild.appendChild(secondChild);

    // Add dragend event listener on map to fade the button once map is not
    // showing the current location.
    window.google.maps.event.addListener(map, 'dragend', function() {
      $('#you_location_img').css('background-position', '0px 0px');
    });

    // Add click event listener on button to get user's location from
    // browser.
    firstChild.addEventListener('click', function() {
      let imgX = '0';
      let animationInterval = setInterval( function () {
        let imgX = imgX === '-18' ? imgX = '0' : imgX = '-18';
        $('#you_location_img').css('background-position', imgX + 'px 0px');
      }, 500);

      // Geolocation.
      if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
          var latlng = new window.google.maps.LatLng(position.coords.latitude, position.coords.longitude);

          // Center map on user's location.
          map.setCenter(latlng);
          // Stop animation.
          clearInterval(animationInterval);
          // Change icon color.
          $('#you_location_img').css('background-position', '-144px 0px');
        });
      }
      else {
        // Stop animation.
        clearInterval(animationInterval);
        // Change icon color to disabled.
        $('#you_location_img').css('background-position', '0px 0px');
      }
    });

    // Add to map.
    controlDiv.index = 1;
    map.controls[control_position].push(controlDiv);
  };

  /**
   * Set coords on map by country.
   */
  setCountryCoords = () => {
    // Get the coords of the country.
    let geocoder = new google.maps.Geocoder();
    geocoder.geocode({
      'address': drupalSettings.country_code
    }, function (results, status) {
      if (status == google.maps.GeocoderStatus.OK) {
        //this.googleMap.setCenter(results[0].geometry.location);
        window.spcMap.setCenter(results[0].geometry.location);
      }
    });
  };

  /**
   * Create google map.
   */
  createGoogleMap = (centerPosition, control_position) => {
    // If corrds not available, try country coords.
    if (centerPosition.lat === undefined) {
      // If address is being edited, get coords from
      // address detail.
      if (this.props.isEditAddress) {
        geocodeAddressToLatLng();
      }
      else {
        this.setCountryCoords();
      }

      // As coords are required for initialize google
      // map object and if user has not allowed location
      // share, we first center the map to middle-east region
      // and then re-center map on the country center.
      centerPosition = {
        'lat': 29.2985,
        'lng': 42.5510
      }
    }

    return new window.google.maps.Map(this.googleMapDiv(), {
      zoom: 14,
      center: centerPosition,
      disableDefaultUI: false,
      mapTypeControl: false,
      streetViewControl: false,
      fullscreenControl: false,
      zoomControlOptions: {
        position: control_position
      },
    })
  }

  render () {
    return (
      <div className='spc-google-map'>
        <div className='spc-location-g-map-search form-type-textfield'>
          <input placeholder={Drupal.t('enter a location')} ref={ref => (this.autocomplete = ref)} id='searchTextField' type='text'/>
        </div>
        <div id='google-map' ref={this.googleMapRef} style={{width: '100%', height: '100%'}}/>
      </div>
    );
  }

}
