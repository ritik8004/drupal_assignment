import React from 'react';
import {
  Map, GoogleApiWrapper, Marker,
} from 'google-maps-react';

export class StoreFinderMap extends React.Component {
  componentDidMount() {
    const { markers, coords } = this.props;

    const storeItems = markers && markers.map((x) => {
      const store = x;
      const distance = google.maps.geometry.spherical.computeDistanceBetween(
        new google.maps.LatLng(coords.lat, coords.lng),
        new google.maps.LatLng(x.geocoordinates.latitude, x.geocoordinates.longitude),
      );
      store.distanceInMiles = this.convertKmToMile(distance);
      return store;
    });

    this.handleStateChange(storeItems);
  }

  handleStateChange = (storeItems) => {
    const { handleStateChange } = this.props;
    handleStateChange(storeItems);
  }

  convertKmToMile = (value) => {
    const realMiles = (value * 0.621371);
    const Miles = Math.floor(realMiles);
    return Miles;
  }

  render() {
    const { google, initialCoords, markers } = this.props;

    return (
      <Map
        google={google}
        zoom={14}
        initialCenter={initialCoords}
      >
        {markers && markers.map((place) => (
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
  apiKey: 'AIzaSyB7aeOgTLzW0zyn3kYWLfWsBB6t2AOvBWE',
  libraries: ['places', 'geometry'],
})(StoreFinderMap);
