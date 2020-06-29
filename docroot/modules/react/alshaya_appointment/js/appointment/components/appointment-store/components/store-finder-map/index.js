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
          {markers && Object.entries(markers).map(([k, marker]) => (
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
            <div className="testing-infowindow">
              <div className="infowindow-header-wrapper">
                <h4>{selectedPlace.name}</h4>
                <span className="distance">{`${selectedPlace.distance} ${Drupal.t('Miles')}`}</span>
              </div>
              <StoreAddress
                address={selectedPlace.address}
              />
              <StoreTiming
                timing={selectedPlace.timing}
              />
            </div>
          </InfoWindow>
        </Map>
      </>
    );
  }
}
