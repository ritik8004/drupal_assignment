import React from 'react';
import {
  Map, Marker, InfoWindow, GoogleApiWrapper,
} from 'google-maps-react';
// eslint-disable-next-line import/no-named-as-default
import InfoPopUp from '../InfoPopup';

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
    const { center, stores } = this.props;
    return (
      <Map
        /* eslint-disable-next-line react/destructuring-assignment */
        google={this.props.google}
        style={{ width: '100%', height: '100%', position: 'relative' }}
        className="map"
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
          />
        ))}
        {showingInfoWindow && (
          <InfoWindow
            marker={activeMarker}
            onClose={this.onInfoWindowClose}
            visible={showingInfoWindow}
          >
            <InfoPopUp selectedPlace={selectedPlace} />
          </InfoWindow>
        )}
      </Map>
    );
  }
}

export default GoogleApiWrapper({
  apiKey: 'AIzaSyBL9faHw5s_vO1sUalcbQv05dzce_71fUY',
})(MultipeMarker);
