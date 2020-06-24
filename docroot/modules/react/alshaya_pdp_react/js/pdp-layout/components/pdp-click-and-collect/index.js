import React from 'react';
import axios from 'axios';
import PdpSectionTitle from '../utilities/pdp-section-title';
import PdpSectionText from '../utilities/pdp-section-text';
import ClickCollectContent from '../pdp-click-and-collect-popup';
import PdpClickCollectSearch from '../pdp-click-and-collect-search';

export default class PdpClickCollect extends React.PureComponent {
  constructor(props) {
    super(props);
    this.searchRef = React.createRef();
    this.autocomplete = null;
    this.searchplaceInput = null;
    this.state = {
      label: 'check in-store availablility:',
      stores: null,
      location: '',
      hideInput: false,
      showNoResult: false,
    };
  }

  componentDidMount() {
    if (!this.autocomplete && !!this.searchRef && !!this.searchRef.current) {
      this.searchplaceInput = this.searchRef.current.getElementsByTagName('input').item(0);
      new Promise(((resolve) => {
        const waitForMapsApi = setInterval(() => {
          if (Drupal.alshayaSpc.maps_api_loading === false) {
            clearInterval(waitForMapsApi);
            resolve();
          }
        }, 100);
      })).then(() => {
        this.autocomplete = new window.google.maps.places.Autocomplete(
          this.searchplaceInput,
          {
            types: [],
            componentRestrictions: { country: drupalSettings.clickNCollect.countryCode },
          },
        );
        this.autocomplete.addListener(
          'place_changed',
          this.placesAutocompleteHandler,
        );
      });
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
        lng: place.geometry.location.lng(),
        location: place.formatted_address,
      });
    }
  };

  /**
   * Fetch available stores for given lat and lng.
   */
  fetchAvailableStores = (coords) => {
    const { productInfo } = drupalSettings;
    let skuItemCode = null;
    if (productInfo) {
      [skuItemCode] = Object.keys(productInfo);
    }
    const baseUrl = window.location.origin;
    const apiUrl = Drupal.url(`stores/product/${skuItemCode}/${coords.lat}/${coords.lng}?type=json`);
    const GET_STORE_URL = `${baseUrl}${apiUrl}`;
    axios.get(GET_STORE_URL)
      .then((res) => {
        if (res.data.all_stores.length !== 0) {
          this.setState({
            stores: res.data.all_stores,
            location: coords.location,
            hideInput: true,
          });
        } else {
          // Show no result div.
          this.setState({
            showNoResult: true,
          });
        }
      });
  };

  render() {
    const {
      label, stores, location, hideInput, showNoResult,
    } = this.state;
    const { cncEnabled } = drupalSettings.clickNCollect;
    const { cncSubtitleAvailable } = drupalSettings.clickNCollect;
    const { cncSubtitleUnavailable } = drupalSettings.clickNCollect;

    const searchField = (
      <PdpClickCollectSearch ref={this.searchRef} defaultValue={location} />
    );

    if (cncEnabled) {
      return (
        <div className="magv2-pdp-click-and-collect-wrapper card">
          <div className="magv2-click-collect-title-wrapper">
            <PdpSectionTitle>
              {Drupal.t('click & collect')}
            </PdpSectionTitle>
          </div>
          <PdpSectionText className="click-collect-detail">
            <span>{cncSubtitleAvailable}</span>
          </PdpSectionText>
          <div className="instore-wrapper">
            <div className="instore-title">{label}</div>
            {hideInput ? (
              <ClickCollectContent
                location={location}
                stores={stores}
              />
            ) : searchField}
            {showNoResult ? (
              <span className="empty-store-list">{Drupal.t('Sorry, No store found for your location.')}</span>
            ) : null}
          </div>
        </div>
      );
    }
    return (
      <p className="cnc-unavailable">{cncSubtitleUnavailable}</p>
    );
  }
}
