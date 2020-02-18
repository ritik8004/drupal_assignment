import React from 'react';

import SectionTitle from '../../../utilities/section-title';
import GMap from './gmap';
import Axios from 'axios';
import { getGlobalCart } from '../../../utilities/get_cart';
import StoreList from '../store-list';

export default class ClickCollect extends React.Component {

  constructor(props) {
    super(props);
    this.searchplaceInput = React.createRef();
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
  getCurrentPosition = () => {
    // If location access is enabled by user.
    try {
      if (navigator && navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(pos => {
          this.fetchAvailableStores({
            lat: pos.coords.latitude,
            lng: pos.coords.longitude,
          });
        });
      }
    }
    catch (e) {
      // Empty.
    }
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

  render() {
    let {coords, store_list} = this.state;

    return(
      <div className="spc-address-form">
        { window.innerWidth > 768 &&
          <div className='spc-address-form-map'>
            <GMap
              coords={this.state.coords}
              onCoordsUpdate={this.fetchAvailableStores}
              markers={store_list}
            />
          </div>
        }
        <div className='spc-address-form-sidebar'>
          <SectionTitle>{Drupal.t('Collection Store')}</SectionTitle>
          <div className='spc-address-form-wrapper'>
            { window.innerWidth < 768 &&
              <div className='spc-address-form-map'>
                <GMap
                  coords={coords}
                  onCoordsUpdate={this.fetchAvailableStores}
                  markers={store_list}
                />
              </div>
            }
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
                  <button className="cc-near-me" id="edit-near-me" onClick={this.getCurrentPosition}>{Drupal.t('Near me')}</button>
                </div>
                <div id="click-and-collect-list-view">
                  <StoreList store_list={store_list}/>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    );
  }

}
