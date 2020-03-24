import React from 'react';
import { renderToString } from 'react-dom/server';
import Gmap from '../../../../utilities/map/Gmap';
import StoreItemInfoWindow from './StoreItemInfowindow';

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

  componentDidMount() {
    // Create map object. Initial map center coordinates
    // can be provided from the caller in props.
    window.spcMap.map.googleMap = this.createGoogleMap();
    this.googleMap.setCurrentMap(window.spcMap.map.googleMap);
    const { markers } = this.props;
    if (markers !== null && markers.length > 0) {
      this.placeMarkers();
    } else {
      this.googleMap.removeMapMarker();
      this.googleMap.setCenter();
    }
  }

  componentDidUpdate(prevProps) {
    const { coords, markers } = this.props;
    if (prevProps.coords !== coords || prevProps.markers !== markers) {
      if (markers !== null && markers.length > 0) {
        this.placeMarkers();
      } else {
        this.googleMap.removeMapMarker();
        this.googleMap.setCenter();
      }
    }
  }

  /**
   * Initiate geocoder.
   */
  initGeoCoder = () => {
    this.geocoder = new google.maps.Geocoder();
  };

  /**
   * Place markers on map.
   */
  placeMarkers = async () => {
    this.googleMap.removeMapMarker();
    const { markers, openSelectedStore } = this.props;
    if (!markers || !markers.length) {
      return;
    }

    // Initiate bounds object.
    this.googleMap.setCurrentMap(window.spcMap.map.googleMap);
    window.spcMap.map.googleMap.bounds = new google.maps.LatLngBounds();
    const self = this;
    await markers.forEach((store, index) => {
      const position = new google.maps.LatLng(parseFloat(store.lat), parseFloat(store.lng));
      const markerConfig = {
        position,
        title: store.name,
        infoWindowContent: renderToString(<StoreItemInfoWindow display="default" store={store} />),
        infoWindowSolitary: true,
        // Require When markers overlap on each other, show the latest one on top,
        zIndex: index + 1,
      };
      // Pass "false" as second param, to show infowindow.
      self.googleMap.setMapMarker(markerConfig, !(window.innerWidth < 768));
      // Add new marker position to bounds.
      window.spcMap.map.googleMap.bounds.extend(position);
    });
    if (openSelectedStore === false) {
      // Auto zoom.
      window.spcMap.map.googleMap.fitBounds(window.spcMap.map.googleMap.bounds);
      // Auto center.
      window.spcMap.map.googleMap.panToBounds(window.spcMap.map.googleMap.bounds);
    }
  }

  /**
   * Create google map.
   */
  createGoogleMap = () => this.googleMap.initMap(this.googleMapRef.current);

  render() {
    return <div id="google-map-cnc" ref={this.googleMapRef} style={{ width: '100%', height: '100%' }} />;
  }
}

export default ClicknCollectMap;
