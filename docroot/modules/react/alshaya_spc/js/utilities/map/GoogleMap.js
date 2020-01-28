import React from 'react';

import {createMarker, createInfoWindow} from './map_utils';
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
    // This data can be passed from caller in props.
    let data = [];

    let control_position = isRTL() === true ? window.google.maps.ControlPosition.RIGHT_BOTTOM : window.google.maps.ControlPosition.LEFT_BOTTOM;

    // This can be called conditionally from props
    // if map points for current location.
    this.setCurrentLocationCoords();

    // Create map object. Initial map center coordinates
    // can be provided from the caller in props.
    this.googleMap = this.createGoogleMap(data[0], control_position);

    // Storing in global so that can be accessed byt parent and others.
    window.spcMap = this.googleMap;

    // Add My location button.
    this.addMyLocationButton(window.spcMap, control_position);

    // This can be passed from props if click on
    // map is allowed or not.
    let mapClickable = false;
    if (mapClickable) {
      this.googleMap.addListener('click', this.onMapClick);
    }

    // If there are multiple markers.
    // This will be the case of CnC and thus can be used
    // conditionally and can be determined by props.
    for (var i = 0; i < data.length; i++) {
      let marker = createMarker({lat: data[i]['lat'], lng: data[i]['lng']}, this.googleMap);
      let infowindow = createInfoWindow(data[i]['content']);
      // When marker is clicked.
      marker.addListener('click', function () {
        infowindow.open(this.googleMap, marker);
      });

      // Add marker to the array.
      this.markers.push(marker);
    }

    // For autocomplete textfield.
    this.autocomplete = new window.google.maps.places.Autocomplete(this.autocompleteTextField(), {
      types: [],
      componentRestrictions: {country: window.drupalSettings.country_code}
    });
    this.autocomplete.addListener('place_changed', this.placesAutocompleteHandler);
  }

  /**
   * Get current location coordinates.
   */
  setCurrentLocationCoords = () => {
    // This can be passed from props if map needs
    // to be centered around current location.
    let centerAroundCurrentLocation = true;
    if (centerAroundCurrentLocation) {
      // If location access is enabled by user.
      if (navigator && navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(pos => {
          let currentCoords = {
            'lat': pos.coords.latitude,
            'lng': pos.coords.longitude,
          };

          this.panMapToGivenCoords(currentCoords);
        });
      }
    }
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
  }

  /**
   * Autocomplete handler for the places list.
   */
  placesAutocompleteHandler = () => {
    const place = this.autocomplete.getPlace();
    this.panMapToGivenCoords(place.geometry.location);
    // Get geocode details for address.
    this.geocodeFromLatLng(place.geometry.location);
  }

  /**
   * When click on map.
   */
  onMapClick = (e) => {
    this.panMapToGivenCoords(e.latLng);
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
   * Create google map.
   */
  createGoogleMap = (centerPosition, control_position) => {
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
          <input placeholder={Drupal.t('Enter a location')} ref={ref => (this.autocomplete = ref)} id='searchTextField' type='text'/>
        </div>
        <div id='google-map' ref={this.googleMapRef} style={{width: '100%', height: '100%'}}/>
      </div>
    );
  }

}
