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
import {
  nearByStores,
  getDistanceBetween,
} from '../utility';
import AddressHoursParent from '../components/AddressHoursParent';

export class StoreFinderList extends React.PureComponent {
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
      loadmore: true,
      page: 10,
      locationName: '',
    };
  }

  componentDidMount() {
    // This will be replace with MDC data api call.
    const { apiUrl } = drupalSettings.storeLabels;
    Axios.get(apiUrl).then((response) => {
      const stores = response.data;
      if (Object.keys(stores).length !== 0) {
        const urlSearchParams = new URLSearchParams(window.location.search);
        const params = Object.fromEntries(urlSearchParams.entries());
        const currentLocation = { lat: +params.latitude, lng: +params.longitude };
        const nearbyStores = stores.items.filter((store) => {
          const otherLocation = { lat: +store.latitude, lng: +store.longitude };
          const distance = getDistanceBetween(currentLocation, otherLocation);
          const proximity = drupalSettings.storeLabels.search_proximity_radius || 5;
          return (distance < proximity) ? store : null;
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
            loadmore: true,
            page: drupalSettings.storeLabels.load_more_item_limit,
            locationName: params.location ? params.location : '',
          },
        );
      }
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

  searchStores = (place) => {
    const currentLocation = JSON.parse(JSON.stringify(place.geometry.location));
    const { stores } = this.state;
    const nearbyStores = nearByStores(stores, currentLocation);
    const prevState = this.state;
    this.setState({ ...prevState, stores: nearbyStores, count: nearbyStores.length });
    window.location.href = Drupal.url(`store-finder/list?location=${place.formatted_address}&latitude=${currentLocation.lat}&longitude=${currentLocation.lng}`);
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
    const nearbyStores = nearByStores(stores, currentLocation);
    if (nearbyStores.length > 0) {
      const prevState = this.state;
      this.setState({ ...prevState, stores: nearbyStores, count: nearbyStores.length });
      window.location.href = Drupal.url(`store-finder/list?latitude=${currentLocation.lat}&longitude=${currentLocation.lng}`);
    }
  }

  fail = () => 'Could not obtain location.'

  toggleOpenClass = (storeId) => {
    const element = document.getElementById(`hours--label-${storeId}`);
    element.classList.toggle('open');
  }

  loadMore = () => {
    const {
      stores,
      page,
    } = this.state;
    this.setState({
      page: (page + drupalSettings.storeLabels.load_more_item_limit),
    });
    if (stores.length < (page + drupalSettings.storeLabels.load_more_item_limit)) {
      this.setState({
        loadmore: false,
      });
    }
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
      zoom,
      page,
      loadmore,
      locationName,
    } = this.state;
    const { google } = this.props;
    return (
      <>
        <div className="c-content">
          <div className="c-content__region">
            <div className="region region__content clearfix">
              <div className="views-exposed-form stores-finder-exposed-form current-view block-store-finder-form">
                <div className="form--inline clearfix">
                  <a className="current-location block-store-finder-form__current-location" onClick={this.findNearMe}>{Drupal.t('Near me')}</a>
                  <div className="store-finder--wrapper block-store-finder-form__form__wrapper">
                    <div className="label--location block-store-finder-form__form__label">
                      <div className="block-store-finder-form__form__label__wrapper icon-search">
                        {Drupal.t('Find stores near')}
                      </div>
                    </div>
                    <div className="input--wrapper block-store-finder-form__input__wrapper">
                      <div className="form-item-geolocation-geocoder-google-places-api input__inner-container">
                        <AutocompleteSearch
                          searchStores={this.searchStores}
                          placeholder={drupalSettings.storeLabels.search_placeholder}
                          locationName={locationName}
                        />
                        <div className="c-input__bar" />
                      </div>
                      <div className="block-store-finder-form__input__submit__wrapper icon-search form-actions js-form-wrapper form-wrapper" id="edit-actions--2">
                        <button className="block-store-finder-form__input__submit button js-form-submit form-submit" id="edit-submit-stores-finder--2" type="button" />
                      </div>
                    </div>
                  </div>
                  <a className="back-to-glossary store-list-back-to-glossary" href={Drupal.url('store-finder')}>{drupalSettings.storeLabels.store_list_label}</a>
                </div>
              </div>
              {stores.length > 0
              && (
              <div className="views-element-container">
                <div className="view-stores-finder view-id-stores_finder view-display-id-page_1">
                  <div className="view-header" />
                  <div className="view-content list-store--detail">
                    {stores.map((store, index) => (
                      <div key={store.id} className={index < page ? 'show' : 'hidden'}>
                        <div className="list-view-locator">
                          <div className="store-row--counter">{(index + 1)}</div>
                          <div className="mobile-only-back-to-glossary store-back-to-glossary">
                            <a href={Drupal.url('store-finder')}>Back</a>
                          </div>
                          <a className="row-title" onClick={() => this.showSpecificPlace(store.id)}>
                            <span className="row-title-store-name">{store.store_name}</span>
                          </a>
                          <div className="views-row">
                            <div className="views-field-field-store-address">
                              <div className="field-content">
                                <AddressHoursParent
                                  type="addressitem"
                                  address={store.address}
                                  classname="address--line2"
                                />
                                <div className="field field--name-field-store-phone field--type-string field--label-hidden field__item">
                                  {store.store_phone}
                                </div>
                              </div>
                            </div>
                            <div className="views-field-field-store-open-hours">
                              <div className="field-content">
                                <div className="hours--wrapper selector--hours2">
                                  <div id={`hours--label-${store.id}`} className="hours--label" onClick={() => this.toggleOpenClass(store.id)}>
                                    {Drupal.t('Opening Hours')}
                                  </div>
                                  <AddressHoursParent
                                    type="hoursitem"
                                    storeHours={store.store_hours}
                                  />
                                </div>
                                <div className="view-on--map">
                                  <a onClick={() => this.getDirection(store)}>{Drupal.t('Get directions')}</a>
                                </div>
                                <div className="get--directions">
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
                    ))}
                  </div>
                  <div className="attachment attachment-after store--map">
                    <div className="views-element-container">
                      <div className="view-stores-finder view-id-stores_finder view-display-id-attachment_1">
                        <div className="geolocation-common-map-container">
                          <Map
                            google={google}
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
                                icon={drupalSettings.alshaya_stores_finder.map.marker_icon_path}
                              />
                            ))}
                            {showingInfoWindow && (
                              <InfoWindow
                                marker={activeMarker}
                                onClose={this.onInfoWindowClose}
                                visible={showingInfoWindow}
                              >
                                <InfoPopUp
                                  selectedPlace={selectedPlace}
                                  storeHours={selectedPlace.openHours}
                                />
                              </InfoWindow>
                            )}
                          </Map>
                        </div>
                      </div>
                    </div>
                  </div>
                  {loadmore
                  && (
                    <div className="view-footer">
                      <div className="load-more-button">
                        <a onClick={this.loadMore}>{Drupal.t('Load More')}</a>
                      </div>
                    </div>
                  )}
                </div>
              </div>
              )}
            </div>
          </div>
        </div>
      </>
    );
  }
}
export default GoogleApiWrapper({
  apiKey: drupalSettings.alshaya_geolocation.api_key,
})(StoreFinderList);
