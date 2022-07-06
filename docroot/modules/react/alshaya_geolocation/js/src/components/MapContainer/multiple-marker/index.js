import React from 'react';
import {
  Map, Marker, InfoWindow, GoogleApiWrapper,
} from 'google-maps-react';
import { InfoPopUp } from '../InfoPopup';

export class MultipeMarker extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      activeMarker: {},
      selectedPlace: {},
      showingInfoWindow: false,
      new_center: {},
      zoom: 10,
    };
  }

  componentDidMount() {
    const { zoom, center } = this.props;
    this.setState((prevState) => ({
      ...prevState,
      zoom,
      newCenter: center,
    }));
  }

  onMarkerClick = (props, marker) => {
    this.setState((prevState) => ({
      ...prevState,
      activeMarker: marker,
      selectedPlace: props,
      showingInfoWindow: true,
      newCenter: props.position,
      zoom: 18,
    }));
  };

  onInfoWindowClose = () => this.setState({
    activeMarker: null,
    showingInfoWindow: false,
  });

  render() {
    const {
      showingInfoWindow,
      activeMarker,
      selectedPlace,
      newCenter,
      zoom,
    } = this.state;
    const { google } = this.props;
    const { center, stores } = this.props;
    return (
      <Map
        google={google}
        initialCenter={center}
        center={newCenter}
        zoom={zoom}
      >
        {stores.map((store, index) => (
          <Marker
            onClick={this.onMarkerClick}
            label={(index + 1).toString()}
            z-index={(index + 1).toString()}
            key={store.id}
            title={store.store_name}
            name={store.store_name}
            openHours={store.store_hours}
            address={store.address}
            position={{ lat: store.latitude, lng: store.longitude }}
            icon={drupalSettings.alshaya_stores_finder.map.marker_icon_path}
          />
        ))}
        {showingInfoWindow && (
          <InfoWindow
            marker={activeMarker}
            onClose={this.onInfoWindowClose}
            visible={showingInfoWindow}
          >
            <InfoPopUp
              selectedPlace={selectedPlace}
              storeHours={selectedPlace.openHours}
            />
          </InfoWindow>
        )}
      </Map>
    );
  }
}

export default GoogleApiWrapper({
  apiKey: drupalSettings.alshaya_geolocation.api_key,
})(MultipeMarker);
