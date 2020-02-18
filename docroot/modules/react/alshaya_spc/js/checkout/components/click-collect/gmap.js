import React from 'react';
import _isEmpty from 'lodash/isEmpty';


import {createMarker, createInfoWindow} from '../../../utilities/map/map_utils';
import { isRTL } from '../../../utilities/rtl';

export default class GMap extends React.Component {

  constructor(props) {
    super(props);
    this.googleMapRef = React.createRef();
    // Global map object.
    this.googleMap = null;
    // Global for list of markers on map.
    this.markers = [];
    this.geoCoder = null;
  }

  /**
   * Initiate geocoder.
   */
  initGeoCoder = () => {
    this.geocoder = new google.maps.Geocoder();
  }

  /**
   * Center rendered map to coords if we already have, or center it to site country.
   */
  centerMap = () => {
    if (!_isEmpty(this.props.coords)) {
      window.spcMap.setCenter(this.props.coords);
      window.spcMap.setZoom(11);
      return;
    }

    this.geocoder.geocode({
      componentRestrictions: {
        country: window.drupalSettings.country_code
      }
    }, function (results, status) {
        if (status == google.maps.GeocoderStatus.OK) {
          // Just center the map and don't do anything.
          window.spcMap.setCenter(results[0].geometry.location);
        }
    });
  }

  componentDidMount() {
    // Initiate geocoder.
    this.initGeoCoder();
    // Create map object. Initial map center coordinates
    // can be provided from the caller in props.
    this.googleMap = this.createGoogleMap({});
    window.spcMap = this.googleMap;
    this.centerMap();
  }

  componentDidUpdate(prevProps, prevState) {
    if (prevProps.coords !== this.props.coords || prevProps.markers !== this.props.markers) {
      this.centerMap();
      this.placeMarkers();
    }
  }

  /**
   * @todo: IN WIP.
   * Removes all markers from map.
   */
  removeAllMarkersFromMap = () => {
    if (!this.markers) {
      return;
    }
    // First clear all existing marker on map.
    for (var i = 0; i < this.markers.length; i++) {
      this.markers[i].setMap(null);
    }
    this.markers = [];
    window.spcMarkers = this.markers;
  }

  /**
   * Place markers on map.
   */
  placeMarkers = async () => {
    let { markers } = this.props;
    if (!markers || !markers.length) {
      return;
    }

    // Initiate bounds object.
    window.spcMap.bounds = new google.maps.LatLngBounds();
    await markers.forEach(function (store) {
      let marker = createMarker({
        lat: parseFloat(store.lat),
        lng: parseFloat(store.lng)
      }, window.spcMap);
      // Add new marker position to bounds.
      window.spcMap.bounds.extend(marker.getPosition());
      // window.spcMap.panToBounds(window.spcMap.bounds);
      // let infowindow = createInfoWindow(markers[i]['content']);
      // // When marker is clicked.
      // marker.addListener('click', function () {
      //   infowindow.open(window.spcMap, marker);
      // });

      // Add marker to the array.
      // this.markers.push(marker);
    });

    // Auto zoom.
    window.spcMap.fitBounds(window.spcMap.bounds);

    // Auto center.
    window.spcMap.panToBounds(window.spcMap.bounds);
  }

  /**
   * Create google map.
   */
  createGoogleMap = (centerPosition) => {
    return new window.google.maps.Map(this.googleMapRef.current, {
      zoom: 9,
      mapTypeControl: true,
      streetViewControl: true,
      fullscreenControl: false,
      zoomControlOptions: {
        position: isRTL() === true ? window.google.maps.ControlPosition.RIGHT_BOTTOM : window.google.maps.ControlPosition.LEFT_BOTTOM
      },
    })
  }

  render () {
    return (
      <div id='google-map-cnc' ref={this.googleMapRef} style={{width: '100%', height: '100%'}} />
    );
  }

}
