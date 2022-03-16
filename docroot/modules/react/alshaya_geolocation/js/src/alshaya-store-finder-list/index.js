import React from 'react';
import Axios from 'axios';
import {
  Map,
  Marker,
  InfoWindow,
  GoogleApiWrapper,
} from 'google-maps-react';
import AutocompleteSearch from '../components/autocomplete-search';
import { InfoPopUp } from '../components/MapContainer/InfoPopup';

export class StoreFinderList extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      stores: [],
      count: 1,
      activeMarker: {},
      selectedPlace: {},
      showingInfoWindow: false,
      center: {},
      zoom: 10,
      open: false,
    };
  }

  componentDidMount() {
    // This will be replace with MDC data api call.
    const { apiUrl } = drupalSettings.cnc;
    Axios.get(apiUrl).then((response) => {
      const stores = response.data;
      const urlSearchParams = new URLSearchParams(window.location.search);
      const params = Object.fromEntries(urlSearchParams.entries());
      const currentLocation = { lat: +params.latitude, lng: +params.longitude };
      const nearbyStores = stores.items.filter((store) => {
        const otherLocation = { lat: +store.latitude, lng: +store.longitude };
        const distance = this.getDistanceBetween(currentLocation, otherLocation);
        return (distance < 5) ? store : null;
      });
      const sorter = (a, b) => (a.store_name.toLowerCase() > b.store_name.toLowerCase() ? 1 : -1);
      nearbyStores.sort(sorter);
      const prevState = this.state;
      this.setState(
        {
          ...prevState,
          stores: nearbyStores.length > 0 ? nearbyStores : stores.items,
          count: nearbyStores.length > 0 ? nearbyStores.length : stores.items.length,
          center: {
            lat: +params.latitude ? +params.latitude : stores.items[0].latitude,
            lng: +params.longitude ? +params.longitude : stores.items[0].longitude,
          },
        },
      );
    });
  }

  onMarkerClick = (props, marker) => {
    this.setState((prevState) => ({
      ...prevState,
      activeMarker: marker,
      selectedPlace: props,
      showingInfoWindow: true,
    }));
  }

  showSpecificPlace = (id) => {
    const btn = document.querySelector('.gm-ui-hover-effect');
    if (btn) {
      btn.click();
    }
    const { stores } = this.state;
    const specificPlace = stores.filter((obj) => obj.id === id);
    this.setState({
      showSpecificPlace: true,
      specificPlace: specificPlace[0],
      showingInfoWindow: false,
      activeMarker: null,
      zoom: 15,
      center: { lat: specificPlace[0].latitude, lng: specificPlace[0].longitude },
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

  searchStores = (place) => {
    const currentLocation = JSON.parse(JSON.stringify(place.geometry.location));
    const { stores } = this.state;
    const nearbyStores = this.nearByStores(stores, currentLocation);
    const prevState = this.state;
    this.setState({ ...prevState, stores: nearbyStores, count: nearbyStores.length });
    window.location.href = `/store-finder/list?latitude=${currentLocation.lat}&longitude=${currentLocation.lng}`;
  }

  findNearMe = () => {
    if (navigator.geolocation) {
      // Call getCurrentPosition with success and failure callbacks
      navigator.geolocation.getCurrentPosition(this.success, this.fail);
    }
  }

  success = (position) => {
    const currentLocation = { lat: position.coords.longitude, lng: position.coords.latitude };
    const { stores } = this.state;
    const nearbyStores = this.nearByStores(stores, currentLocation);
    if (nearbyStores.length > 0) {
      const prevState = this.state;
      this.setState({ ...prevState, stores: nearbyStores, count: nearbyStores.length });
      window.location.href = `/store-finder/list?latitude=${currentLocation.lat}&longitude=${currentLocation.lng}`;
    }
  }

  nearByStores = (stores, currentLocation) => {
    const nearbyStores = stores.filter((store) => {
      const otherLocation = { lat: +store.latitude, lng: +store.longitude };
      const distance = this.getDistanceBetween(currentLocation, otherLocation);
      return (distance < 5) ? store : null;
    });
    return nearbyStores;
  }

  fail = () => 'Could not obtain location.'

  getDistanceBetween = (location1, location2) => {
    // The math module contains a function
    // named toRadians which converts from
    // degrees to radians.

    const lon1 = (parseInt((location1.lng), 10) * Math.PI) / 180;
    const lon2 = (parseInt((location2.lng), 10) * Math.PI) / 180;
    const lat1 = (parseInt((location1.lat), 10) * Math.PI) / 180;
    const lat2 = (parseInt((location1.lat), 10) * Math.PI) / 180;

    // Haversine formula
    const dlon = lon2 - lon1;
    const dlat = lat2 - lat1;
    const a = (Math.sin(dlat / 2) ** 2)
      + Math.cos(lat1) * Math.cos(lat2)
      * (Math.sin(dlon / 2) ** 2);

    const c = 2 * Math.asin(Math.sqrt(a));
    // Radius of earth in kilometers.
    const r = 6371;
    // calculate the result
    return (c * r);
  }

  showAllStores = () => {
    window.location.href = '/store-finder/';
  }

  toggleOpenClass = () => {
    this.setState((prevState) => ({
      ...prevState,
      open: !prevState.open,
    }));
  }

  getDirection = (store) => {
    window.open(`https://www.google.com/maps/dir/Current+Location/${store.latitude},${store.longitude}`, '_blank');
  }

  render() {
    const {
      stores,
      showingInfoWindow,
      activeMarker,
      selectedPlace,
      center,
      open,
      zoom,
    } = this.state;
    const { google } = this.props;
    return (
      <>
        <div className="l-container">
          <div onClick={this.findNearMe}>{Drupal.t('Near me')}</div>
          <AutocompleteSearch placeholder={Drupal.t('Enter a location')} searchStores={(place) => this.searchStores(place)} />
          <div onClick={this.showAllStores}>{Drupal.t('List all the stores')}</div>
        </div>
        {stores.length > 0
        && (
        <div className="l-container">
          <div>
            <div>{Drupal.t('select a store to see details')}</div>
            {stores.map((store) => (
              <div key={store.id}>
                <div>
                  <a className="row-title" onClick={() => this.showSpecificPlace(store.id)}>
                    <span>{store.store_name}</span>
                  </a>
                  <div className="views-row">
                    <div className="views-field-field-store-address">
                      <div className="field-content">
                        <div className="address--line2">
                          {store.address.map((item) => (
                            <div key={item.code}>
                              {item.code === 'address_building_segment' ? <span>{item.label}</span> : null}
                              {item.code === 'street' ? <span>{item.value}</span> : null}
                            </div>
                          ))}
                        </div>
                        <div className="field field--name-field-store-phone field--type-string field--label-hidden field__item">
                          {store.store_phone}
                        </div>
                      </div>
                    </div>
                    <div className="views-field-field-store-open-hours">
                      <div className="field-content">
                        <div className="hours--wrapper selector--hours">
                          <div>
                            <div className={open ? 'hours--label open' : 'hours--label'} onClick={this.toggleOpenClass}>
                              {Drupal.t('Opening Hours')}
                            </div>
                            <div className="open--hours">
                              {store.store_hours.map((item) => (
                                <div key={item.code}>
                                  <span className="key-value-key">{item.label}</span>
                                  <span className="key-value-value">{item.value}</span>
                                </div>
                              ))}
                            </div>
                          </div>
                        </div>
                        <div className="view-on--map">
                          <a onClick={() => this.getDirection(store)}>{Drupal.t('Get directions')}</a>
                        </div>
                        <div className="get--directions">
                          <div>
                            <a
                              className="device__desktop"
                              onClick={() => this.getDirection(store)}
                            >
                              {Drupal.t('Get directions')}
                            </a>
                            <a className="device__tablet" onClick={() => this.getDirection(store)}>
                              {Drupal.t('Get directions')}
                            </a>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            ))}
          </div>
          <div className="view-content" style={{ height: '500px' }}>
            <Map
              google={google}
              style={{ width: '100%', height: '100%', position: 'relative' }}
              className="map"
              initialCenter={center}
              center={center}
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
                  position={{ lat: store.latitude, lng: store.longitude }}
                  address={store.address}
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
          </div>
        </div>
        )}
      </>
    );
  }
}
export default GoogleApiWrapper({
  apiKey: drupalSettings.alshaya_geolocation.api_key,
})(StoreFinderList);
