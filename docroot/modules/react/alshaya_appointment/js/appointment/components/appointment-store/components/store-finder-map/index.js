import React from 'react';
import {
  Map, GoogleApiWrapper, Marker, InfoWindow,
} from 'google-maps-react';
import StoreAddress from '../store-address';
import StoreTiming from '../store-timing';

export class StoreFinderMap extends React.Component {
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
    const { showingInfoWindow } = this.state;
    if (showingInfoWindow) {
      this.setState({
        activeMarker: null,
        showingInfoWindow: false,
      });
    }
  };

  render() {
    const { google, initialCoords, markers } = this.props;
    const { activeMarker, showingInfoWindow, selectedPlace } = this.state;

    return (
      <>
        <Map
          google={google}
          zoom={drupalSettings.alshaya_appointment.store_finder.zoom}
          initialCenter={initialCoords}
          onClick={this.onMapClicked}
        >
          {markers && Object.entries(markers).map(([k, marker]) => (
            <Marker
              key={marker.locationExternalId}
              name={marker.name}
              address={marker.address}
              timing={marker.storeTiming}
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
              <h4>{selectedPlace.name}</h4>
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

export default GoogleApiWrapper({
  apiKey: drupalSettings.alshaya_appointment.google_map_api_key,
  libraries: ['places', 'geometry'],
})(StoreFinderMap);
