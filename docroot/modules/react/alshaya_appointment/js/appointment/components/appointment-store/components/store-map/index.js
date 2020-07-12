import React from 'react';
import { renderToString } from 'react-dom/server';
import StoreItemInfoWindow from './StoreItemInfoWindow';

class StoreMap extends React.Component {
  constructor(props) {
    super(props);
    this.googleMapRef = React.createRef();
    // Global for list of markers on map.
    this.markers = [];
    this.geoCoder = null;
  }

  componentDidMount() {
    // Create map object. Initial map center coordinates
    // can be provided from the caller in props.
    window.appointmentMap.map.googleMap = this.createGoogleMap();
    window.appointmentMap.setCurrentMap(window.appointmentMap.map.googleMap);
    const { markers } = this.props;
    window.appointmentMap.removeMapMarker();
    if (markers && markers.length > 0) {
      this.placeMarkers();
    } else {
      window.appointmentMap.setCenter();
    }
  }

  componentDidUpdate(prevProps) {
    const { coords, markers } = this.props;
    if (prevProps.coords !== coords || prevProps.markers !== markers) {
      window.appointmentMap.removeMapMarker();
      if (markers !== null && markers.length > 0) {
        this.placeMarkers();
      } else {
        window.appointmentMap.setCenter();
      }
    }
  }

  /**
   * Place markers on map.
   */
  placeMarkers = async () => {
    window.appointmentMap.removeMapMarker();
    const { markers, openSelectedStore } = this.props;
    if (!markers || !markers.length) {
      return;
    }

    // Initiate bounds object.
    window.appointmentMap.setCurrentMap(window.appointmentMap.map.googleMap);
    window.appointmentMap.map.googleMap.bounds = new google.maps.LatLngBounds();
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
      window.appointmentMap.setMapMarker(markerConfig, !(window.innerWidth < 768));
      // Add new marker position to bounds.
      window.appointmentMap.map.googleMap.bounds.extend(position);
    });
    if (openSelectedStore === false) {
      // Auto zoom.
      window.appointmentMap.map.googleMap.fitBounds(window.appointmentMap.map.googleMap.bounds);
      // Auto center.
      window.appointmentMap.map.googleMap.panToBounds(window.appointmentMap.map.googleMap.bounds);
    }
  }

  /**
   * Create google map.
   */
  createGoogleMap = () => window.appointmentMap.initMap(this.googleMapRef.current);

  render() {
    return <div id="google-map-appointment-booking" ref={this.googleMapRef} style={{ width: '600px', height: '100%' }} />;
  }
}

export default StoreMap;
