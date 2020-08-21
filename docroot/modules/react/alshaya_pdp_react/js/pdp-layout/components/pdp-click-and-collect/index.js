import React from 'react';
import parse from 'html-react-parser';
import PdpSectionTitle from '../utilities/pdp-section-title';
import PdpSectionText from '../utilities/pdp-section-text';
import PdpClickCollectSearch from '../pdp-click-and-collect-search';
import { fetchAvailableStores } from '../../../utilities/pdp_layout';
import {
  setupAccordionHeight,
  allowMaxContent,
  removeMaxHeight,
} from '../../../utilities/sidebarCardUtils';
import ClickCollectStoreDetail from '../pdp-click-and-collect-store-detail';
import ConditionalView from '../../../common/components/conditional-view';

export default class PdpClickCollect extends React.PureComponent {
  constructor(props) {
    super(props);
    this.searchRef = React.createRef();
    this.expandRef = React.createRef();
    this.autocomplete = null;
    this.searchplaceInput = null;
    this.state = {
      stores: null,
      location: '',
      hideInput: false,
      showMore: false,
      showNoResult: false,
      open: false,
    };
  }

  componentDidMount() {
    // Accordion setup.
    setupAccordionHeight(this.expandRef);

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
        if (res.data.all_stores['#stores'].length !== 0) {
          this.setState({
            stores: res.data.all_stores['#stores'],
            location: place.formatted_address,
            hideInput: true,
            showMore: false,
          });
        } else {
          // Show no result div.
          this.setState({
            showNoResult: true,
          });
        }
      });
    }
    // Allow maximum content for now.
    allowMaxContent(this.expandRef);
  };

  toggleShowMore = () => {
    this.setState((prevState) => ({
      showMore: !prevState.showMore,
    }));
  };

  showClickCollectContent = () => {
    const { open } = this.state;

    if (open) {
      this.setState({
        open: false,
      });
      // Remove any maxcontent allowance.
      removeMaxHeight(this.expandRef);
      this.expandRef.current.style.removeProperty('max-height');
    } else {
      this.setState({
        open: true,
      });
      const maxHeight = this.expandRef.current.getAttribute('data-max-height');
      this.expandRef.current.style.maxHeight = maxHeight;
    }
  };

  render() {
    const {
      stores, location, hideInput, showNoResult, open, showMore,
    } = this.state;

    // Add correct class.
    const expandedState = open === true ? 'show' : '';
    const { cncEnabled } = drupalSettings.clickNCollect;
    const { cncSubtitleAvailable } = drupalSettings.clickNCollect;
    const { cncSubtitleUnavailable } = drupalSettings.clickNCollect;

    const searchField = (
      <PdpClickCollectSearch ref={this.searchRef} defaultValue={location} />
    );

    if (cncEnabled) {
      let label = Drupal.t('Check in-store availability:');
      let storesContent = {};
      let storeCountLabel = '';

      if (hideInput) {
        label = Drupal.t('In-store availability:');
        storesContent = stores
          .filter((store, key) => key < (showMore ? stores.length : 2))
          .map((store, key) => (
            <ClickCollectStoreDetail key={store.code} index={key + 1} store={store} />
          ));
        storeCountLabel = Drupal.t('@count store(s) near !variable', {
          '@count': stores.length,
          '!variable': `<span className="location" onClick="{this.showSearchInput}">${location}</span>`,
        });
      }

      return (
        <div
          className="magv2-pdp-click-and-collect-wrapper card fadeInUp"
          style={{ animationDelay: '1.2s' }}
          ref={this.expandRef}
        >
          <div
            className={`magv2-click-collect-title-wrapper title ${expandedState}`}
            onClick={() => this.showClickCollectContent()}
          >
            <PdpSectionTitle>
              {Drupal.t('click & collect')}
            </PdpSectionTitle>
            <div className="magv2-accordion" />
          </div>
          <div className="magv2-click-collect-content-wrapper content">
            <PdpSectionText className="click-collect-detail">
              <span>{cncSubtitleAvailable}</span>
            </PdpSectionText>
            <div className="instore-wrapper">
              <div className="instore-title">{label}</div>
              {hideInput ? (
                <>
                  <div className="store-count-label">{parse(storeCountLabel)}</div>
                  <div className="magv2-click-collect-results">{storesContent}</div>

                  <ConditionalView condition={stores.length > 2}>
                    <div className="magv2-click-collect-show-link" onClick={this.toggleShowMore}>
                      {Drupal.t(showMore ? 'Show-less' : 'Show-more')}
                    </div>
                  </ConditionalView>
                </>
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
      <div
        className="magv2-pdp-click-and-collect-wrapper card disabled fadeInUp"
        style={{ animationDelay: '1.2s' }}
        ref={this.expandRef}
      >
        <div
          className={`magv2-click-collect-title-wrapper title ${expandedState}`}
          onClick={() => this.showClickCollectContent()}
        >
          <PdpSectionTitle>
            {Drupal.t('click & collect')}
          </PdpSectionTitle>
          <div className="magv2-accordion" />
        </div>
        <div className="magv2-click-collect-content-wrapper content">
          <p className="cnc-unavailable">{cncSubtitleUnavailable}</p>
        </div>
      </div>
    );
  }
}
