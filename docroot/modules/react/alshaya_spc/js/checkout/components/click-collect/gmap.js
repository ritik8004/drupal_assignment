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

  initGeoCoder = () => {
    this.geocoder = new google.maps.Geocoder();
  }

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
    }, function(results, status){
        if (status == google.maps.GeocoderStatus.OK) {
          // console.log(results[0].geometry.location);
          window.spcMap.setCenter(results[0].geometry.location);
          return;
        }
    });
  }

  componentDidMount() {
    // This data can be passed from caller in props.
    let data = [];
    // Initiate geocoder.
    this.initGeoCoder();

    // Create map object. Initial map center coordinates
    // can be provided from the caller in props.
    this.googleMap = this.createGoogleMap({});
    window.spcMap = this.googleMap;
    this.centerMap();
  }

  componentDidUpdate() {
    this.centerMap();
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
