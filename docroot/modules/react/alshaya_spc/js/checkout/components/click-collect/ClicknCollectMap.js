import React from 'react';
import { renderToString } from 'react-dom/server'

import _isEmpty from 'lodash/isEmpty';

import Gmap from '../../../utilities/map/Gmap';
import StoreItemInfoWindow from '../store-item-infowindow';

class ClicknCollectMap extends React.Component {

  constructor(props) {
    super(props);
    this.googleMapRef = React.createRef();
    // Global map object.
    this.googleMap = new Gmap();
    window.spcMap = this.googleMap;
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

  componentDidMount() {
    // Create map object. Initial map center coordinates
    // can be provided from the caller in props.
    window.spcMap.googleMap = this.createGoogleMap({});
    if (this.props.markers) {
      this.placeMarkers();
    }
    else {
      this.googleMap.setCenter();
    }
  }

  componentDidUpdate(prevProps, prevState) {
    if (prevProps.coords !== this.props.coords || prevProps.markers !== this.props.markers) {
      if (this.props.markers) {
        this.placeMarkers();
      }
      else {
        this.googleMap.setCenter();
      }
    }
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
    this.googleMap.setCurrentMap(window.spcMap.googleMap);
    this.googleMap.removeMapMarker();
    window.spcMap.googleMap.bounds = new google.maps.LatLngBounds();
    let self = this;
    await markers.forEach(function (store, index) {

      let position = new google.maps.LatLng(parseFloat(store.lat), parseFloat(store.lng));
      let markerConfig = {
        position: position,
        title: store.name,
        infoWindowContent: renderToString(<StoreItemInfoWindow store={store} />),
        infoWindowSolitary: true,
        label: (index + 1).toString(),
        // Require When markers overlap on each other, show the latest one on top,
        zIndex: index + 1
      };
      self.googleMap.setMapMarker(markerConfig, false);

      // Add new marker position to bounds.
      window.spcMap.googleMap.bounds.extend(position);
    });
    // Auto zoom.
    window.spcMap.googleMap.fitBounds(window.spcMap.googleMap.bounds);
    // Auto center.
    window.spcMap.googleMap.panToBounds(window.spcMap.googleMap.bounds);
  }

  /**
   * Create google map.
   */
  createGoogleMap = () => {
    return this.googleMap.initMap(this.googleMapRef.current);
  }

  render () {
    return (
      <div id='google-map-cnc' ref={this.googleMapRef} style={{width: '100%', height: '100%'}} />
    );
  }

}

export default ClicknCollectMap;
