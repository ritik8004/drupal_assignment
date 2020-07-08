import React from 'react';
import {
  Map, Marker, InfoWindow,
} from 'google-maps-react';
import StoreAddress from '../store-address';
import StoreTiming from '../store-timing';

export default class StoreFinderMap extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      activeMarker: {},
      selectedPlace: {},
      showingInfoWindow: false,
    };
  }

  onMarkerClick = (props, marker) => {
    this.setState({
      activeMarker: marker,
      selectedPlace: props,
      showingInfoWindow: true,
    });
    this.handleStoreSelect();
  }

  onInfoWindowClose = () => this.setState({
    activeMarker: null,
    showingInfoWindow: false,
  });

  onMapClicked = () => {
    this.setState((prevState) => {
      if (!prevState.showingInfoWindow) return null;
      return {
        activeMarker: null,
        showingInfoWindow: false,
      };
    });
  };

  handleStoreSelect = (e) => {
    const { handleStoreSelect } = this.props;
    handleStoreSelect(e);
  }

  render() {
    const { google, markers } = this.props;
    const { activeMarker, showingInfoWindow, selectedPlace } = this.state;
    const { latitude, longitude } = drupalSettings.alshaya_appointment.store_finder;

    return (
      <>
        <Map
          google={google}
          zoom={drupalSettings.alshaya_appointment.store_finder.zoom}
          initialCenter={{
            lat: latitude,
            lng: longitude,
          }}
          onClick={this.onMapClicked}
        >
          {markers && Object.entries(markers).map(([, marker]) => (
            <Marker
              key={marker.locationExternalId}
              name={marker.name}
              address={marker.address}
              timing={marker.storeTiming}
              distance={marker.distanceInMiles}
              position={{
                lat: marker.geocoordinates.latitude,
                lng: marker.geocoordinates.longitude,
              }}
              onClick={this.onMarkerClick}
            />
          ))}
          <InfoWindow
            marker={activeMarker}
            onClose={this.onInfoWindowClose}
            visible={showingInfoWindow}
          >
            <div className="appointment-map-store-wrapper">
              <span className="appointment-store-name">
                <span className="appointment-store-name-wrapper">
                  <span className="store-name">
                    {selectedPlace.name}
                  </span>
                  <span className="store-distance">
                    {`${selectedPlace.distance} ${Drupal.t('Miles')}`}
                  </span>
                </span>
                <span className="appointment-map-list-close" />
              </span>
              <div className="store-address-content">
                <div className="store-address">
                  <StoreAddress
                    address={selectedPlace.address}
                  />
                </div>
                <div className="store-delivery-time">
                  <StoreTiming
                    timing={selectedPlace.timing}
                  />
                </div>
              </div>
            </div>
          </InfoWindow>
        </Map>
      </>
    );
  }
}
