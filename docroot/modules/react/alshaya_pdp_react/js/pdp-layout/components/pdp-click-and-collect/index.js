import React from 'react';
import parse from 'html-react-parser';
import PdpSectionTitle from '../utilities/pdp-section-title';
import PdpSectionText from '../utilities/pdp-section-text';
import PdpClickCollectSearch from '../pdp-click-and-collect-search';
import { fetchAvailableStores } from '../../../utilities/pdp_layout';
import setupAccordionHeight from '../../../utilities/sidebarCardUtils';
import ClickCollectStoreDetail from '../pdp-click-and-collect-store-detail';
import ConditionalView from '../../../common/components/conditional-view';
import ClickCollectSVG from '../../../svg-component/cc-svg';

export default class PdpClickCollect extends React.PureComponent {
  constructor(props) {
    super(props);
    this.searchRef = React.createRef();
    this.expandRef = React.createRef();
    this.autocomplete = null;
    this.autocompleteInit = false;
    this.searchplaceInput = null;
    this.state = {
      stores: [],
      location: '',
      hideInput: false,
      showMore: false,
      open: true,
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
        this.searchplaceInput.addEventListener('keyup', this.keyUpHandler);
      });
    }
  }

  updateHeightOnAjax() {
    const { hideInput, open } = this.state;
    if (hideInput && open) {
      const element = this.expandRef.current;
      this.expandRef.current.style.removeProperty('max-height');
      setTimeout(() => {
        element.setAttribute('data-max-height', `${element.offsetHeight}px`);
        element.style.maxHeight = `${element.offsetHeight}px`;
      });
    }
  }

  /**
   * Autocomplete handler for the places list.
   */
  placesAutocompleteHandler = () => {
    const { productInfo } = this.props;
    const place = this.autocomplete.getPlace();
    if (typeof place !== 'undefined' && typeof place.geometry !== 'undefined') {
      fetchAvailableStores(productInfo, {
        lat: place.geometry.location.lat(),
        lng: place.geometry.location.lng(),
        location: place.formatted_address,
      }).then((res) => {
        if (res.data.all_stores['#stores'] !== undefined && res.data.all_stores['#stores'].length !== 0) {
          this.setState({
            stores: res.data.all_stores['#stores'],
            location: place.formatted_address,
            hideInput: true,
            showMore: false,
          });
          document.getElementById('click-n-collect-search-field').classList.add('hidden');
        } else {
          // Show no result div.
          this.setState({
            stores: [],
            hideInput: false,
          });
        }

        this.updateHeightOnAjax();
      });
    }
  };

  toggleShowMore = (event) => {
    this.setState((prevState) => ({
      showMore: !prevState.showMore,
    }));
    this.updateHeightOnAjax();
    event.stopPropagation();
  };

  showClickCollectContent = () => {
    const { open } = this.state;

    if (open) {
      this.setState({
        open: false,
      });
      this.expandRef.current.classList.add('close-card');
    } else {
      this.setState({
        open: true,
      });
      this.expandRef.current.classList.remove('close-card');
    }
  };

  showSearchInput = (e) => {
    if (e.target.tagName.toLowerCase() === 'span') {
      document.getElementById('click-n-collect-search-field').classList.remove('hidden');
    }

    this.updateHeightOnAjax();
  };

  /**
   * Keyup handler for the search input.
   */
  keyUpHandler = (e) => {
    if (e.target.value.length >= 2 && !this.autocompleteInit) {
      this.autocomplete = new window.google.maps.places.Autocomplete(
        this.searchplaceInput,
        {
          types: ['geocode'],
          componentRestrictions: { country: window.drupalSettings.country_code },
        },
      );

      this.autocomplete.addListener(
        'place_changed',
        this.placesAutocompleteHandler,
      );
      this.autocompleteInit = true;
    } else if (e.target.value.length < 2) {
      e.preventDefault();
      jQuery('.pac-container').remove();
      google.maps.event.clearInstanceListeners(this.autocomplete);
      this.autocompleteInit = false;
    }
  };

  render() {
    const {
      stores, location, hideInput, open, showMore,
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
      let label = Drupal.t('Check in-store availability');
      let storesContent = {};
      let storeCountLabel = '';

      if (hideInput) {
        label = Drupal.t('In-store availability');
        storesContent = stores
          .filter((store, key) => key < (showMore ? stores.length : 2))
          .map((store, key) => (
            <ClickCollectStoreDetail key={store.code} index={key + 1} store={store} />
          ));
        storeCountLabel = Drupal.t('@count store(s) near !variable', {
          '@count': stores.length,
          '!variable': `<span class="click-n-collect-selected-location">${location}</span>`,
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
              <span className="magv2-card-icon-svg">
                <ClickCollectSVG />
              </span>
              {Drupal.t('Click & Collect')}
            </PdpSectionTitle>
            <div className="magv2-accordion" />
          </div>
          <div className="magv2-click-collect-content-wrapper content">
            <PdpSectionText className="click-collect-detail">
              <span>{cncSubtitleAvailable}</span>
            </PdpSectionText>
            <div className="instore-wrapper">
              <div className="instore-title">{`${label}:`}</div>

              <ConditionalView condition={stores && stores.length > 0}>
                <div className="store-count-label" onClick={this.showSearchInput}>{parse(storeCountLabel)}</div>
              </ConditionalView>

              <span id="click-n-collect-search-field" className={hideInput ? 'hidden' : ''}>
                {searchField}
              </span>

              <ConditionalView condition={stores && stores.length > 0}>
                <div className="magv2-click-collect-results fadeInUp">{storesContent}</div>

                <ConditionalView condition={stores.length > 2}>
                  <div className="magv2-click-collect-show-link fadeInUp" onClick={this.toggleShowMore}>
                    {Drupal.t(showMore ? 'Show-less' : 'Show-more')}
                  </div>
                </ConditionalView>
              </ConditionalView>

              <ConditionalView condition={hideInput && (!(stores) || stores.length === 0)}>
                <span className="empty-store-list">{Drupal.t('Sorry, No store found for your location.')}</span>
              </ConditionalView>
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
            <span className="magv2-card-icon-svg">
              <ClickCollectSVG />
            </span>
            {Drupal.t('Click & Collect')}
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
