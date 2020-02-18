import React from 'react';
import { renderToString } from 'react-dom/server'

import _isEmpty from 'lodash/isEmpty';

import Gmap from '../../../utilities/map/Gmap';
import StoreItemInfoWindow from '../store-item-infowindow';

export default class GMap extends React.Component {

  constructor(props) {
    super(props);
    this.googleMapRef = React.createRef();
    // Global map object.
    this.googleMap = new Gmap();
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
    window.spcMap = this.createGoogleMap({});
    this.googleMap.setCenter();
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
    this.googleMap.setCurrentMap(window.spcMap);
    window.spcMap.bounds = new google.maps.LatLngBounds();
    let self = this;
    await markers.forEach(function (store, index) {

      let position = new google.maps.LatLng(parseFloat(store.lat), parseFloat(store.lng));
      let markerConfig = {
        position: position,
        title: store.name,
        infoWindowContent: renderToString(<StoreItemInfoWindow store={store} />),
        infoWindowSolitary: false,
        label: (index).toString(),
        zIndex: index + 1
      };
      self.googleMap.setMapMarker(markerConfig, false);

      // Add new marker position to bounds.
      window.spcMap.bounds.extend(position);
    });
    // Auto zoom.
    window.spcMap.fitBounds(window.spcMap.bounds);
    // Auto center.
    window.spcMap.panToBounds(window.spcMap.bounds);
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
