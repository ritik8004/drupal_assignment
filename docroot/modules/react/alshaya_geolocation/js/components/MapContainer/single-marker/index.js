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
    const { store, center } = this.props;
    return (
      <>
        <Map
          /* eslint-disable-next-line react/destructuring-assignment */
          google={this.props.google}
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
  apiKey: 'AIzaSyBL9faHw5s_vO1sUalcbQv05dzce_71fUY',
})(SingleMarker);
