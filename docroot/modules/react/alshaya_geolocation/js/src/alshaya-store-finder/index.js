import React from 'react';
import Axios from 'axios';
import AutocompleteSearch from '../components/autocomplete-search';
import SingleMarkerMap from '../components/MapContainer/single-marker';
import MultipeMarkerMap from '../components/MapContainer/multiple-marker';
import { ListItem } from '../components/ListItem';

export class StoreFinder extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      stores: [],
      count: 1,
      showSpecificPlace: false,
      showListingView: true,
      showMapView: false,
      specificPlace: {},
      center: {},
      zoom: 10,
      groupedStores: [],
    };
  }

  componentDidMount() {
    // This will be replace with MDC data api call.
    const { apiUrl } = drupalSettings.cnc;
    Axios.get(apiUrl).then((response) => {
      const stores = response.data;
      const storeSort = (a, b) => (
        a.store_name.toLowerCase() > b.store_name.toLowerCase() ? 1 : -1
      );
      stores.items.sort(storeSort);
      const prevState = this.state;
      this.setState(
        {
          ...prevState,
          stores: stores.items,
          count: stores.total_count,
          center: { lat: stores.items[0].latitude, lng: stores.items[0].longitude },
        },
        () => {
          const currentState = this.state;
          const obj = currentState.stores.reduce((acc, c) => {
            const letter = c.store_name[0];
            acc[letter] = (acc[letter] || []).concat({ id: c.id, store_name: c.store_name });
            return acc;
          }, {});
          this.setState({
            groupedStores: obj,
          });
        },
      );
    });
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
    });
  }

  hideSpecificPlace = () => {
    const btn = document.querySelector('.gm-ui-hover-effect');
    if (btn) {
      btn.click();
    }
    this.setState({
      showSpecificPlace: false,
      specificPlace: {},
      showingInfoWindow: false,
      activeMarker: null,
    });
  }

  showListingView = () => {
    window.location.href = 'store-finder';
  }

  showMapView = () => {
    this.setState({
      showListingView: false,
      showMapView: true,
    });
  }

  searchStores = (place) => {
    const currentLocation = JSON.parse(JSON.stringify(place.geometry.location));
    const { stores } = this.state;
    const nearbyStores = this.nearByStores(stores, currentLocation);
    const prevState = this.state;
    this.setState({ ...prevState, stores: nearbyStores, count: nearbyStores.length });
    window.location.href = `store-finder/list?latitude=${currentLocation.lat}&longitude=${currentLocation.lng}`;
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

  fail = () => 'Could not obtain location.';

  nearByStores = (stores, currentLocation) => {
    const nearbyStores = stores.filter((store) => {
      const otherLocation = { lat: +store.latitude, lng: +store.longitude };
      const distance = this.getDistanceBetween(currentLocation, otherLocation);
      return (distance < 5) ? store : null;
    });
    return nearbyStores;
  }

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

  render() {
    const {
      stores,
      showSpecificPlace,
      specificPlace,
      center,
      showListingView,
      showMapView,
      zoom,
      groupedStores,
    } = this.state;
    const seperate = Math.ceil(Object.keys(groupedStores).length / 2);
    const firstColumn = Object.entries(groupedStores).slice(0, seperate);
    const secondColumn = Object.entries(groupedStores).slice(seperate);
    return (
      <>
        <div className="l-container">
          {showSpecificPlace
            ? (
              <div>
                <div onClick={this.findNearMe}>{Drupal.t('Near me')}</div>
                <AutocompleteSearch placeholder={Drupal.t('Enter a location')} searchStores={(place) => this.searchStores(place)} />
                <div onClick={this.showAllStores}>{Drupal.t('List all H&M stores')}</div>
              </div>
            ) : (
              <div>
                <div onClick={this.findNearMe}>{Drupal.t('Near me')}</div>
                <AutocompleteSearch placeholder={Drupal.t('Enter a location')} searchStores={(place) => this.searchStores(place)} />
                <div onClick={this.showListingView}>{Drupal.t('Show Listing View')}</div>
                <div onClick={this.showMapView}>{Drupal.t('Show Map View')}</div>
              </div>
            ) }
        </div>
        {showSpecificPlace
          ? (
            <div className="individual--store">
              <div className="view-content">
                <div className="list-view-locator">
                  <div className="back-link">
                    <a href="#" onClick={this.hideSpecificPlace}>Back</a>
                  </div>
                  <ListItem specificPlace={specificPlace} />
                </div>
              </div>
              <div className="view-content" style={{ height: '500px' }}>
                <SingleMarkerMap store={specificPlace} center={center} />
              </div>
            </div>
          )
          : (
            <div className="l-container">
              {showListingView
              && (
                <div>
                  <div className="view-content">
                    <div>select a store to see details</div>
                    <div className="warpper">
                      <div className="col-1">
                        {firstColumn.map((value) => (
                          <div key={value[0]}>
                            <div>{value[0]}</div>
                            <div>
                              {value[1].map((item) => (
                                <div
                                  key={item.id}
                                  onClick={() => this.showSpecificPlace(item.id)}
                                >
                                  {item.store_name}
                                </div>
                              ))}
                            </div>
                          </div>
                        ))}
                      </div>
                      {secondColumn
                      && (
                      <div className="col-2">
                        {secondColumn.map((value) => (
                          <div key={value[0]}>
                            <div>{value[0]}</div>
                            <div>
                              {value[1].map((item) => (
                                <div
                                  key={item.id}
                                  onClick={() => this.showSpecificPlace(item.id)}
                                >
                                  {item.store_name}
                                </div>
                              ))}
                            </div>
                          </div>
                        ))}
                      </div>
                      )}
                    </div>
                  </div>
                </div>
              )}
              {showMapView
              && (
                <div className="view-content" style={{ height: '500px' }}>
                  <MultipeMarkerMap center={center} zoom={zoom} stores={stores} />
                </div>
              )}
            </div>
          )}
      </>
    );
  }
}
export default StoreFinder;
