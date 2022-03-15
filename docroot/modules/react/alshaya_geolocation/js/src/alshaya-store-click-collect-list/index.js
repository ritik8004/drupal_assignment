import React from 'react';
import Axios from 'axios';
import { ClickCollectPopup } from '../components/store-click-collect-popup';
import AutocompleteSearch from '../components/autocomplete-search';
import { ListItemClick } from '../components/ListItemClick';

export class StoreClickCollectList extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      stores: [],
      count: 1,
      showListingView: false,
      specificPlace: {},
      center: {},
      newCenter: {},
      zoom: 10,
      active: false,
      open: false,
      showAutomcomplete: true,
      area: '',
      isModalOpen: false,
    };
    this.openModal = this.openModal.bind(this);
    this.closeModal = this.closeModal.bind(this);
  }

  componentDidMount() {
    // This will be replace with MDC data api call.
    const { apiUrl } = drupalSettings.cnc;
    Axios.get(apiUrl).then((response) => {
      const stores = response.data;
      const prevState = this.state;
      this.setState(
        {
          ...prevState,
          stores: stores.items,
          count: stores.total_count,
          center: { lat: stores.items[0].latitude, lng: stores.items[0].longitude },
        },
      );
    });
  }

  toggleClass() {
    const { active } = this.state;
    this.setState({ active: !active });
  }

  searchStores = (place) => {
    if (place.geometry !== undefined) {
      const currentLocation = JSON.parse(JSON.stringify(place.geometry.location));
      const { stores } = this.state;
      const nearbyStores = this.nearByStores(stores, currentLocation);
      const prevState = this.state;
      this.setState({
        ...prevState,
        area: place,
        stores: nearbyStores,
        count: nearbyStores.length,
        showListingView: true,
        showAutomcomplete: false,
      });
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

  openModal() {
    this.setState({ isModalOpen: true });
  }

  closeModal() {
    this.setState({ isModalOpen: false });
  }

  render() {
    const {
      stores,
      showListingView,
      area,
      showAutomcomplete,
      active,
      isModalOpen,
    } = this.state;
    const shorts = stores.slice(0, 2);
    const cncLabels = drupalSettings.cac;
    return (
      <>
        <div className="delivery-options-wrapper">
          <div className="field__content">
            <div className="click-collect c-accordion-delivery-options ui-accordion ui-widget ui-helper-reset">
              <h3 className="c-accordion__title ui-accordion-header ui-corner-top ui-state-default ui-accordion-icons location-js-initiated ui-accordion-header-active" onClick={() => this.toggleClass()}>
                <span className="ui-accordion-header-icon ui-icon ui-icon-triangle-1-s">{cncLabels.title}</span>
                <span className="subtitle">{cncLabels.subtitle}</span>
              </h3>
              <div className="c-accordion_content ui-accordion-content ui-corner-bottom ui-helper-reset ui-widget-content">
                <div className={active ? 'active' : ''}>
                  <div className="click-collect-empty-selection" />
                  <div className="click-collect-form">
                    <div className="text">
                      <p>
                        {Drupal.t('This service is ')}
                        <strong>{Drupal.t('free')}</strong>
                        {Drupal.t(' of charge.')}
                      </p>
                      <div>{cncLabels.help_text}</div>
                      {showAutomcomplete
                        ? (
                          <div className="store-finder-form-wrapper">
                            <div id="search-store" className="search-store">
                              <form className="alshaya-stores-available-stores">
                                <span className="label">{Drupal.t('Check in-store availability')}</span>
                                <div>
                                  <AutocompleteSearch
                                    placeholder={Drupal.t('Enter a location')}
                                    searchStores={(place) => this.searchStores(place)}
                                  />
                                  <button className="search-stores-button" type="button">search stores</button>
                                </div>
                              </form>
                            </div>
                          </div>
                        )
                        : (
                          <div className="available_store">
                            <div className="available-store-text">
                              <span className="store-available-at-title">
                                {Drupal.t('Available at ')}
                                {stores.length}
                                {Drupal.t(' stores near ')}
                              </span>
                              <div className="google-store-location">{area.formatted_address}</div>
                              <div className="change-location-link" onClick={() => this.setState({ showAutomcomplete: true })}>{Drupal.t(' change')}</div>
                            </div>
                          </div>
                        )}
                    </div>
                  </div>
                  {showListingView
                  && (
                    <div className="click-collect-top-stores">
                      <div id="click-and-collect-list-view">
                        <ul>
                          {shorts.map((store, index) => (
                            <li>
                              <span key={store.id} className="select-store">
                                <div className="store-sequence">{(index) + 1}</div>
                                <ListItemClick specificPlace={store} isPopup />
                              </span>
                            </li>
                          ))}
                        </ul>
                      </div>
                      {(stores.length > 3
                      && (
                        <div className="other-stores-link" onClick={this.openModal}>
                          {Drupal.t('Other stores nearby')}
                        </div>
                      ))}
                    </div>
                  )}
                </div>
              </div>
              <ClickCollectPopup
                stores={stores}
                isOpen={isModalOpen}
                onClose={this.closeModal}
              />
            </div>
          </div>
        </div>
      </>
    );
  }
}
export default StoreClickCollectList;
