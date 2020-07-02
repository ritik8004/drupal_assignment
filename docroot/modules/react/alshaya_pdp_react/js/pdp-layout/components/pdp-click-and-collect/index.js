import React from 'react';
import PdpSectionTitle from '../utilities/pdp-section-title';
import PdpSectionText from '../utilities/pdp-section-text';
import PdpClickCollectSearch from '../pdp-click-and-collect-search';
import { fetchAvailableStores } from '../../../utilities/pdp_layout';
import ClickCollectContent from '../pdp-click-and-collect-popup';

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
      fetchAvailableStores({
        lat: place.geometry.location.lat(),
        lng: place.geometry.location.lng(),
        location: place.formatted_address,
      }).then((res) => {
        if (res.data.all_stores.length !== 0) {
          this.setState({
            stores: res.data.all_stores,
            location: place.formatted_address,
            hideInput: true,
          });
        } else {
          // Show no result div.
          this.setState({
            showNoResult: true,
          });
        }
      });
    }
  };

  toggleShowMore = () => {
    this.setState((prevState) => ({
      showMore: !prevState.showMore,
    }));
  }

  showClickCollectContent = () => {
    document.querySelector('.magv2-pdp-click-and-collect-wrapper').classList.toggle('show-click-collect-content');
  }

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
            <div className="magv2-accordion" onClick={this.showClickCollectContent} />
          </div>
          <div className="magv2-click-collect-content-wrapper">
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
        </div>
      );
    }
    return (
      <p className="cnc-unavailable">{cncSubtitleUnavailable}</p>
    );
  }
}
