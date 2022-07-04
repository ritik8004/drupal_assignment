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
          zoom={15}
        >
          <Marker
            label={(1).toString()}
            z-index={(1)}
            title={store.store_name}
            name={store.store_name}
            openHours={store.store_hours}
            position={{ lat: store.latitude, lng: store.longitude }}
            icon={drupalSettings.alshaya_stores_finder.map.marker_icon_path}
          />
        </Map>
      </>
    );
  }
}

export default GoogleApiWrapper({
  apiKey: drupalSettings.alshaya_geolocation.api_key,
})(SingleMarker);
