import React from 'react';
import { renderToString } from 'react-dom/server';
import StoreItemInfoWindow from './StoreItemInfowindow';
import globalGmap from '../../../../utilities/map/Gmap';
import { getCncMapIcon } from '../../../../utilities/cnc_util';

class ClicknCollectMap extends React.Component {
  constructor(props) {
    super(props);
    this.googleMapRef = React.createRef();
    // Global map object.
    this.googleMap = globalGmap.create();
  }

  componentDidMount() {
    // Create map object. Initial map center coordinates
    // can be provided from the caller in props.
    // Initiate bounds object.
    this.createGoogleMap();
    const { markers } = this.props;
    this.googleMap.removeMapMarker();
    if (typeof markers !== 'undefined' && markers !== null && markers.length > 0) {
      this.placeMarkers();
    } else {
      this.googleMap.setCenter();
    }
  }

  componentDidUpdate(prevProps) {
    const { coords, markers } = this.props;
    if (prevProps.coords !== coords || prevProps.markers !== markers) {
      this.googleMap.removeMapMarker();
      if (markers !== null && markers.length > 0) {
        this.placeMarkers();
      } else {
        this.googleMap.setCenter();
      }
    }
  }

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
    this.googleMap.map.googleMap.bounds = new google.maps.LatLngBounds();
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
      self.googleMap.setMapMarker(markerConfig, !(window.innerWidth < 768), getCncMapIcon(store));
      // Add new marker position to bounds.
      this.googleMap.map.googleMap.bounds.extend(position);
    });
    if (openSelectedStore === false) {
      // Auto zoom.
      this.googleMap.map.googleMap.fitBounds(this.googleMap.map.googleMap.bounds);
      // Auto center.
      this.googleMap.map.googleMap.panToBounds(this.googleMap.map.googleMap.bounds);
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
