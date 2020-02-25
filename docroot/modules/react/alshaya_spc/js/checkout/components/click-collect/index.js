import React from 'react';

import SectionTitle from '../../../utilities/section-title';
import Axios from 'axios';
import { getGlobalCart } from '../../../utilities/get_cart';
import StoreList from '../store-list';
import ClicknCollectMap from './ClicknCollectMap';
import _find from 'lodash/find';
import _findIndex from 'lodash/findIndex';
import { ClicknCollectContext } from '../../.../../../context/ClicknCollect';
import SelectedStore from '../selected-store';
import { showLoader, removeLoader } from '../../../utilities/checkout_util';

class ClickCollect extends React.Component {
  static contextType = ClicknCollectContext;

  constructor(props) {
    super(props);
    this.searchplaceInput = React.createRef();
    this.cncListView = React.createRef();
    this.cncMapView = React.createRef();
    this.state = {
      openSelectedStore: this.props.openSelectedStore || false,
    }
  }

  componentDidMount() {
    // For autocomplete text field.
    this.autocomplete = new window.google.maps.places.Autocomplete(this.searchplaceInput.current, {
      types: [],
      componentRestrictions: { country: window.drupalSettings.country_code }
    });
    this.autocomplete.addListener('place_changed', this.placesAutocompleteHandler);
    // Ask for location access when we don't have any coords.
    if (this.context.coords !== null) {
      let skipOpenMarker = false;
      if (!this.context.storeList) {
        skipOpenMarker = true;
        this.fetchAvailableStores(this.context.coords);
      }

      if (this.state.openSelectedStore && !skipOpenMarker) {
        this.showOpenMarker(this.context.storeList);
      }
    }
    else if (!this.context.coords) {
      this.getCurrentPosition();
    }
  }

  /**
   * Autocomplete handler for the places list.
   */
  placesAutocompleteHandler = () => {
    const place = this.autocomplete.getPlace();
    if (typeof place !== 'undefined' && typeof place.geometry !== 'undefined') {
      this.fetchAvailableStores({
        lat: place.geometry.location.lat(),
        lng: place.geometry.location.lng()
      });
    }
  }

  /**
   * Get current location coordinates.
   */
  getCurrentPosition = (e) => {
    if (e) {
      e.preventDefault();
    }
    // If location access is enabled by user.
    try {
      if (navigator && navigator.geolocation) {
        e.target.classList.add('active');
        navigator.geolocation.getCurrentPosition(this.LocationSuccess, this.LocationFail, { timeout: 1000 });
      }
    }
    catch (e) {
      // Empty.
    }
    return false;
  }

  LocationSuccess = pos => {
    this.fetchAvailableStores({
      lat: pos.coords.latitude,
      lng: pos.coords.longitude,
    });
  }

  LocationFail = error => {
    if (error.code == error.PERMISSION_DENIED) {
      // Display dialog when location access is blocked from browser.
      let message = Drupal.t('We need permission to locate your nearest stores. You can enable location services in your browser settings.');
      let locationErrorDialog = Drupal.dialog('<div id="drupal-modal">' + message + '</div>', {
        modal: true,
        width: "auto",
        height: "auto",
        title: Drupal.t('Location access denied'),
        dialogClass: 'location-disabled-notice',
        resizable: false,
        closeOnEscape: true,
        close: function close(event) {
          Drupal.dialog(event.target).close();
        }
      });
      locationErrorDialog.showModal();
    }
  }

  /**
   * Fetch available stores for given lat and lng.
   */
  fetchAvailableStores = async (coords) => {
    let { cart_id } = getGlobalCart();
    const GET_STORE_URL = `/cnc/stores/${cart_id}/${coords.lat}/${coords.lng}`;
    showLoader();
    let storesResponse = await Axios.get(GET_STORE_URL);
    if (storesResponse && storesResponse.data) {
      removeLoader();
      if (this.state.openSelectedStore) {
        // Wait for all markers are placed on map, before we open a marker.
        let self = this;
        setTimeout(() => {
          self.showOpenMarker(storesResponse.data);
        }, 500);
      }
      this.context.updateCoordsAndStoreList(coords, storesResponse.data.length > 0 ? storesResponse.data : null);
    }
    else {
      this.context.updateCoords(coords);
    }
  }

  selectStore = (e, store_code) => {
    e.preventDefault();
    // Find the store object with the given store-code from the store list.
    let store = _find(this.context.storeList, { code: store_code });
    this.context.updateSelectStore(store);
    this.setState({
      openSelectedStore: true
    });
  }

  toggleStoreView = (e, activeView) => {
    e.preventDefault();
    if (activeView === 'map') {
      this.cncMapView.current.style.display = "block";
      this.cncListView.current.style.display = "none";
      this.refreshMap();
    }
    else {
      this.cncMapView.current.style.display = "none";
      this.cncListView.current.style.display = "block";
    }
    return false;
  };

  // View selected store on map.
  storeViewOnMapSelected = function (makerIndex) {
    let map = window.spcMap;
    // Zoom the current map to store location.
    // map.googleMap.setZoom(11);
    // Make the marker by default open.
    google.maps.event.trigger(map.map.mapMarkers[makerIndex], 'click');
    // Pan Google maps to accommodate the info window.
    map.googleMap.panBy(0, -150);
  };

  showOpenMarker = (storeList) => {
    if (!this.context.selectedStore) {
      return;
    }

    let index = _findIndex(storeList, { code: this.context.selectedStore.code });
    this.storeViewOnMapSelected(index);
  }

  refreshMap = () => {
    let map = window.spcMap;
    // Adjust the map, when we trigger the map view.
    google.maps.event.trigger(map.googleMap, 'resize');
    // Auto zoom.
    map.googleMap.fitBounds(map.googleMap.bounds);
    // Auto center.
    map.googleMap.panToBounds(map.googleMap.bounds);
  }

  render() {
    let { coords, storeList, selectedStore } = this.context;
    let { openSelectedStore } = this.state;

    let mapView = (
      <ClicknCollectMap
        coords={coords}
        onCoordsUpdate={this.fetchAvailableStores}
        markers={storeList}
      />
    );

    return (
      <div className="spc-address-form">
        {window.innerWidth > 768 &&
          <div className='spc-address-form-map'>
            {mapView}
          </div>
        }
        <div className='spc-cnc-address-form-sidebar'>
          <SectionTitle>{Drupal.t('Collection Store')}</SectionTitle>
          <div className='spc-cnc-address-form-wrapper'>
            <div className='spc-cnc-address-form-content' style={{ display: openSelectedStore ? 'none' : 'block' }}>
              <SectionTitle>{Drupal.t('find your nearest store')}</SectionTitle>
              <div className='spc-cnc-location-search-wrapper'>
                <div className='spc-cnc-store-search-form-item'>
                  <input
                    ref={this.searchplaceInput}
                    className="form-search"
                    type="search"
                    id="edit-store-location"
                    name="store_location"
                    size="60"
                    maxLength="128"
                    placeholder={Drupal.t('enter a location')}
                    autoComplete="off"
                  />
                </div>
                <button className="cc-near-me" id="edit-near-me" onClick={(e) => this.getCurrentPosition(e)}>{Drupal.t('Near me')}</button>
              </div>
              {window.innerWidth < 768 &&
                <div className='toggle-store-view'>
                  <button className="stores-list-view" onClick={(e) => this.toggleStoreView(e, 'list')}>
                    {Drupal.t('List view')}
                  </button>
                  <button className="stores-map-view" onClick={(e) => this.toggleStoreView(e, 'map')}>
                    {Drupal.t('Map view')}
                  </button>
                </div>
              }
              <div id="click-and-collect-list-view" ref={this.cncListView}>
                <StoreList
                  store_list={storeList}
                  onStoreClick={this.storeViewOnMapSelected}
                  onSelectStore={this.selectStore}
                />
              </div>
              {window.innerWidth < 768 &&
                <div className='click-and-collect-map-view' style={{ display: 'none', width: '100%', height: '500px' }} ref={this.cncMapView}>
                  {mapView}
                </div>
              }
            </div>
            <SelectedStore store={selectedStore} open={openSelectedStore} />
          </div>
        </div>
      </div>
    );
  }

}

export default ClickCollect;
