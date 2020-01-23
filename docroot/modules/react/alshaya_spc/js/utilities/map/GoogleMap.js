import React from 'react';

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

    // This can be called conditionally from props
    // if map points for current location.
    this.setCurrentLocationCoords();

    // Create map object. Initial map center coordinates
    // can be provided from the caller in props.
    this.googleMap = this.createGoogleMap(data[0]);

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
      let marker = this.createMarker({lat: data[i]['lat'], lng: data[i]['lng']}, this.googleMap);
      let infowindow = this.createInfoWindow(data[i]['content']);
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
    var marker = this.createMarker(coords, this.googleMap);
    this.googleMap.panTo(marker.getPosition());
    this.markers.push(marker);
  }

  /**
   * Autocomplete handler for the places list.
   */
  placesAutocompleteHandler = () => {
    const place = this.autocomplete.getPlace();
    this.panMapToGivenCoords(place.geometry.location);
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
  }

  /**
   * Create info window.
   */
  createInfoWindow = (content) => {
    return new window.google.maps.InfoWindow({
      content: content
    });
  }

  /**
   * Create marker.
   */
  createMarker = (position, map) => {
    return new window.google.maps.Marker({
      position: position,
      map: map,
      icon: '' // This can be later dynamic based on HD or CnC.
    })
  }

  /**
   * Create google map.
   */
  createGoogleMap = (centerPosition) => {
    return new window.google.maps.Map(this.googleMapDiv(), {
      zoom: 14,
      center: centerPosition,
      disableDefaultUI: false,
      mapTypeControl: false,
      streetViewControl: false,
      fullscreenControl: false
    })
  }

  render () {
    return (
      <div>
        <div>
          <input
            placeholder={Drupal.t('Enter a location')}
            ref={ref => (this.autocomplete = ref)}
            id='searchTextField'
            type='text'
          />
        </div>
        <div
          id='google-map'
          ref={this.googleMapRef}
          style={{width: '100%', height: '500px'}}
        />
      </div>
    );
  }

}
