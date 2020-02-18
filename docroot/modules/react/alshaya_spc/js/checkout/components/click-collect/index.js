import React from 'react';

import SectionTitle from '../../../utilities/section-title';
import GMap from './gmap';
// import GoogleMap from '../../../utilities/map/GoogleMap';
// import {getArea, getBlock, createMarker, getMap} from '../../../utilities/map/map_utils';

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
  }

  /**
   * Autocomplete handler for the places list.
   */
  placesAutocompleteHandler = () => {
    const place = this.autocomplete.getPlace();
    if (typeof place !== 'undefined' && typeof place.geometry !== 'undefined') {
      coords = {
        lat: place.geometry.location.lat(),
        lng: place.geometry.location.lng()
      };
    }

    // this.panMapToGivenCoords(place.geometry.location);
    // Get geocode details for address.
    // this.geocodeFromLatLng(place.geometry.location);
  }

  /**
   * When user click on deliver to current location.
   */
  deliverToCurrentLocation = () => {

  }

  /**
   * Get current location coordinates.
   */
  getCurrentPosition = () => {
    // If location access is enabled by user.
    try {
      if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(successCall, ErrorCall, {timeout: 10000});
      }
    }
    catch (e) {
      // Empty.
    }
    if (navigator && navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(pos => {
        let currentCoords = {
          'lat': pos.coords.latitude,
          'lng': pos.coords.longitude,
        };

        this.fetchAvailableStores(currentCoords);
      });
    }
  }

  fetchAvailableStores(coords) {

  }



  render() {
    return(
      <div className="spc-address-form">
        { window.innerWidth > 768 &&
          <div className='spc-address-form-map'>
          </div>
        }
        <div className='spc-address-form-sidebar'>
          <SectionTitle>{Drupal.t('Collection Store')}</SectionTitle>
          <div className='spc-address-form-wrapper'>
            { window.innerWidth < 768 &&
              <div className='spc-address-form-map'>
                <GMap />
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
                <div id="click-and-collect-list-view"></div>
              </form>
            </div>
          </div>
        </div>
      </div>
    );
  }

}
