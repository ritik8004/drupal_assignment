import React from 'react';

import SectionTitle from '../../../utilities/section-title';
import Axios from 'axios';
import { getGlobalCart } from '../../../utilities/get_cart';
import StoreList from '../store-list';
import ClicknCollectMap from './ClicknCollectMap';

export default class ClickCollect extends React.Component {

  constructor(props) {
    super(props);
    this.searchplaceInput = React.createRef();
    this.cncListView = React.createRef();
    this.cncMapView = React.createRef();
    this.state = {
      coords: {},
      store_list: null
    };
  }

  componentDidMount() {
    // For autocomplete textfield.
    this.autocomplete = new window.google.maps.places.Autocomplete(this.searchplaceInput.current, {
      types: [],
      componentRestrictions: {country: window.drupalSettings.country_code}
    });
    this.autocomplete.addListener('place_changed', this.placesAutocompleteHandler);
    this.getCurrentPosition();
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
        navigator.geolocation.getCurrentPosition(
          pos => {
            this.fetchAvailableStores({
              lat: pos.coords.latitude,
              lng: pos.coords.longitude,
            });
          },
          error => {
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
          },
          {
            timeout: 10000
          });
      }
    }
    catch (e) {
      // Empty.
    }
    return false;
  }

  /**
   * Fetch available stores for given lat and lng.
   */
  fetchAvailableStores = async (coords) => {
    let {cart_id} = getGlobalCart();
    const GET_STORE_URL = `/cnc/stores/${cart_id}/${coords.lat}/${coords.lng}`;

    let storesResponse = await Axios.get(GET_STORE_URL);
    if (storesResponse && storesResponse.data) {
      this.setState({
        store_list: storesResponse.data
      });
    }
  }

  // View selected store on map.
  storeViewOnMapSelected = function (makerIndex) {
    // Adjust the map, when we trigger the map view.
    // google.maps.event.trigger(map.googleMap, 'resize');
    let map = window.spcMap;
    // Zoom the current map to store location.
    map.googleMap.setZoom(11);
    // Make the marker by default open.
    google.maps.event.trigger(map.map.mapMarkers[makerIndex], 'click');
    // Get the lat/lng of current store to center the map.
    // var newLocation = new google.maps.LatLng(parseFloat(StoreObj.lat), parseFloat(StoreObj.lng));
    // Set the google map center.
    // map.googleMap.setCenter(newLocation);
    // Pan Google maps to accommodate the info window.
    map.googleMap.panBy(0, -150);
  };

  toggleStoreView = (e, activeView) => {
    e.preventDefault();
    if (activeView === 'map') {
      this.cncMapView.current.style.display = "block";
      this.cncListView.current.style.display = "none";
      let map = window.spcMap;
        // Adjust the map, when we trigger the map view.
      google.maps.event.trigger(map.googleMap, 'resize');
      map.googleMap.fitBounds(map.googleMap.bounds);
      map.googleMap.panToBounds(map.googleMap.bounds);
      // Zoom the current map to store location.
      // map.googleMap.setZoom(9);
    }
    else {
      this.cncMapView.current.style.display = "none";
      this.cncListView.current.style.display = "block";
    }
    return false;
  };

  render() {
    let {coords, store_list} = this.state;

    let mapView = (
      <ClicknCollectMap
        coords={coords}
        onCoordsUpdate={this.fetchAvailableStores}
        markers={store_list}
      />
    );

    return(
      <div className="spc-address-form">
        { window.innerWidth > 768 &&
          <div className='spc-address-form-map'>
            { mapView }
          </div>
        }
        <div className='spc-address-form-sidebar'>
          <SectionTitle>{Drupal.t('Collection Store')}</SectionTitle>
          <div className='spc-address-form-wrapper'>
            <div className='spc-address-form-content'>
              <div>{Drupal.t('Find your nearest store')}</div>
              <form className='spc-address-add' onSubmit={this.handleSubmit}>
                <div>
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
                  <button className="cc-near-me" id="edit-near-me" onClick={(e) => this.getCurrentPosition(e)}>{Drupal.t('Near me')}</button>
                </div>
                { window.innerWidth < 768 &&
                  <div className='toggle-store-view'>
                    <button className="stores-list-view" onClick={(e) => this.toggleStoreView(e, 'list')}>
                      {Drupal.t('List view')}
                    </button>
                    <button  className="stores-map-view"  onClick={(e) => this.toggleStoreView(e, 'map')}>
                      {Drupal.t('Map view')}
                    </button>
                  </div>
                }
                <div id="click-and-collect-list-view" ref={this.cncListView}>
                  <StoreList store_list={store_list} onStoreClick={this.storeViewOnMapSelected}/>
                </div>
                { window.innerWidth < 768 &&
                  <div className='click-and-collect-map-view' style={{display: 'none', width: '100%', height: '500px' }} ref={this.cncMapView}>
                    { mapView }
                  </div>
                }
              </form>
            </div>
          </div>
        </div>
      </div>
    );
  }

}
