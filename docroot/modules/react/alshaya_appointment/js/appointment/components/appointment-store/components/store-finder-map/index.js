import React from 'react';
import {
  Map, GoogleApiWrapper, Marker,
} from 'google-maps-react';

export class StoreFinderMap extends React.Component {
  render() {
    const { google, initialCoords, markers } = this.props;

    return (
      <Map
        google={google}
        zoom={14}
        initialCenter={initialCoords}
      >
        {markers && Object.entries(markers).map(([k, place]) => (
          <Marker
            key={place.locationExternalId}
            lat={place.geocoordinates.latitude}
            lng={place.geocoordinates.longitude}
          />
        ))}
      </Map>
    );
  }
}

export default GoogleApiWrapper({
  apiKey: drupalSettings.alshaya_appointment.google_map_api_key,
  libraries: ['places', 'geometry'],
})(StoreFinderMap);
