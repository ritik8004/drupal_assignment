import React from 'react';
import {
  Map, Marker, GoogleApiWrapper,
} from 'google-maps-react';

export class SingleMarker extends React.Component {
  constructor(props) {
    super(props);
    this.state = {};
  }

  render() {
    const { google } = this.props;
    const { store, center } = this.props;
    return (
      <>
        <Map
          google={google}
          initialCenter={{ lat: store.latitude, lng: store.longitude }}
          center={center}
          className="map map--store"
          zoom={15}
        >
          <Marker
            label={(1).toString()}
            z-index={(1)}
            title={store.store_name}
            name={store.store_name}
            openHours={store.store_hours}
            position={{ lat: store.latitude, lng: store.longitude }}
          />
        </Map>
      </>
    );
  }
}

export default GoogleApiWrapper({
  apiKey: drupalSettings.alshaya_geolocation.api_key,
})(SingleMarker);
