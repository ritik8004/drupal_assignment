import React from 'react';
import parse from 'html-react-parser';
import { ClicknCollectContext } from '../../../context/ClicknCollect';
import createFetcher from '../../../utilities/api/fetcher';
import { fetchClicknCollectStores } from '../../../utilities/api/requests';
import {
  getDefaultMapCenter,
  getLocationAccess,
  removeFullScreenLoader,
  showFullScreenLoader,
} from '../../../utilities/checkout_util';
import SectionTitle from '../../../utilities/section-title';
import SelectedStore from './components/SelectedStore';
import StoreList from './components/StoreList';
import ClicknCollectMap from './components/ClicknCollectMap';
import ToggleButton from './components/ToggleButton';
import LocationSearchForm from './components/LocationSearchForm';
import DeviceView from '../../../common/components/device-view';
import FullScreenSVG from '../../../svg-component/full-screen-svg';
import {
  requestFullscreen,
  isFullScreen,
  exitFullscreen,
} from '../../../utilities/map/fullScreen';
import CheckoutMessage from '../../../utilities/checkout-message';
import getStringMessage from '../../../utilities/strings';
import { smoothScrollTo } from '../../../utilities/smoothScroll';
import { getUserLocation } from '../../../utilities/map/map_utils';
import globalGmap from '../../../utilities/map/Gmap';
import dispatchCustomEvent from '../../../utilities/events';
import {
  getCncModalTitle,
  getCncModalDescription,
  getCncModalButtonText,
} from '../../../utilities/cnc_util';
import collectionPointsEnabled from '../../../../../js/utilities/pudoAramaxCollection';
import logger from '../../../../../js/utilities/logger';

class ClickCollect extends React.Component {
  static contextType = ClicknCollectContext;

  constructor(props) {
    super(props);
    this.searchRef = React.createRef();
    this.cncListView = React.createRef();
    this.cncMapView = React.createRef();

    this.mapStoreList = React.createRef();
    this.autocomplete = null;
    this.autocompleteInit = false;
    this.searchplaceInput = null;
    this.nearMeBtn = null;
    this.googleMap = globalGmap.create();

    const { openSelectedStore } = this.props;
    this.state = {
      openSelectedStore: openSelectedStore || false,
      mapFullScreen: false,
    };
  }

  componentDidMount() {
    // For autocomplete text field.
    const { openSelectedStore } = this.state;
    const {
      coords,
      selectedStore,
      updateModal,
      storeList,
      locationAccess,
      outsideCountryError,
    } = this.context;
    updateModal(true);

    if (!this.autocomplete && !!this.searchRef && !!this.searchRef.current) {
      this.searchplaceInput = this.searchRef.current.getElementsByTagName('input').item(0);
      this.searchplaceInput.addEventListener('keyup', this.keyUpHandler);
      this.nearMeBtn = this.searchRef.current.getElementsByTagName('button').item(0);
    }

    if (coords !== null && typeof storeList !== 'undefined' && storeList.length === 0) {
      this.fetchAvailableStores(coords);
    }

    // Ask for location access when we don't have any coords.
    if (coords !== null && openSelectedStore) {
      this.showSelectedMarker();
    }

    // Show "select this store" button, if a store is selected.
    if (selectedStore && openSelectedStore === false) {
      this.showOpenMarker();
      this.selectStoreButtonVisibility(true);
    }
    // On marker click.
    document.addEventListener('markerClick', this.mapMarkerClick);

    if (outsideCountryError) {
      smoothScrollTo('.spc-cnc-address-form-sidebar .spc-checkout-section-title');
    }

    if (locationAccess === false || outsideCountryError === true) {
      // Adjust list height so it is scrollable, when we have a location error.
      this.dynamicListHeightWhenLocationError();
    }
  }

  componentDidUpdate() {
    const {
      outsideCountryError,
      locationAccess,
    } = this.context;

    if (outsideCountryError) {
      smoothScrollTo('.spc-cnc-address-form-sidebar .spc-checkout-section-title');
    } else {
      this.resetListHeight();
    }

    if (locationAccess === false || outsideCountryError === true) {
      // Adjust list height so it is scrollable, when we have a location error.
      this.dynamicListHeightWhenLocationError();
    }
  }

  componentWillUnmount() {
    document.removeEventListener('markerClick', this.mapMarkerClick);
  }

  dynamicListHeightWhenLocationError = () => {
    // In mobile the sidebar is fullscreen, and entire view is scrollable.
    // This fix is needed only for tablet and desktop which show the CnC as modal.
    if (window.innerWidth >= 768) {
      // The Title of modal. `Collection Store`.
      const modalTitle = document.querySelector('.spc-cnc-stores-list-map > .spc-checkout-section-title').offsetHeight;
      // The modal/sidebar height.
      const modalHeight = document.querySelector('.spc-cnc-address-form-sidebar').offsetHeight;
      // The message container which has error/warning along with its 10px top margin.
      const messageContainer = document.querySelector('.spc-cnc-address-form-wrapper > .spc-messages-container.click-n-collect-store-modal').offsetHeight + 10;
      // The subtitle for the form `Find Your Nearest Store`.
      const subTitle = document.querySelector('.spc-cnc-address-form-content > .spc-checkout-section-title').offsetHeight;
      // The search form along with its 10px bottom margin.
      const searchForm = document.querySelector('.spc-cnc-location-search-wrapper').offsetHeight + 10;
      // The sticky CTA.
      const storeSelectCTA = document.querySelector('.spc-cnc-stores-list-map + .spc-cnc-store-actions').offsetHeight;
      // Store List height = Modal height - height of all other elements.
      const listHeight = modalHeight
        - modalTitle - messageContainer - subTitle - searchForm - storeSelectCTA;
      document.getElementById('click-and-collect-list-view').style.height = `${listHeight}px`;
    }
  };

  resetListHeight = () => {
    // Remove the height set in dynamicListHeightWhenLocationError(), on error dismissal.
    if (window.innerWidth >= 768) {
      document.getElementById('click-and-collect-list-view').style.removeProperty('height');
    }
  }

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

  mapMarkerClick = (e) => {
    const index = e.detail.markerSettings.zIndex - 1;
    const allStores = this.cncListView.current.querySelectorAll('.select-store');
    this.removeClassForAll(allStores, 'selected');

    this.cncListView.current.querySelector(`[data-index="${index}"]`).classList.add('selected');
    this.selectStoreButtonVisibility(true);
    if (window.innerWidth < 768 && this.cncMapView.current !== null && this.cncMapView.current.style.display === 'block') {
      this.toggleFullScreen(true);
      const allMapListStores = this.mapStoreList.current.querySelectorAll('.select-store');
      this.removeClassForAll(allMapListStores, 'selected');
      this.mapStoreList.current.querySelector(`[data-index="${index}"]`).classList.add('selected');
    }
  }

  /**
   * Remove class from all elements of given selector.
   */
  removeClassForAll = (selector, className) => {
    [].forEach.call(selector, (el) => {
      el.classList.remove(className);
    });
  }

  /**
   * Autocomplete handler for the places list.
   */
  placesAutocompleteHandler = () => {
    const place = this.autocomplete.getPlace();
    this.changeNearMeButtonStatus('in-active');
    if (typeof place !== 'undefined' && typeof place.geometry !== 'undefined') {
      this.fetchAvailableStores({
        lat: place.geometry.location.lat(),
        lng: place.geometry.location.lng(),
      });
    }
  };

  changeNearMeButtonStatus = (status) => {
    if (status === 'active') {
      this.nearMeBtn.classList.add('active');
      this.nearMeBtn.disabled = true;
      return;
    }

    if (status === 'in-active') {
      this.nearMeBtn.classList.remove('active');
      this.nearMeBtn.disabled = false;
    }
  }

  /**
   * Get current location coordinates.
   */
  getCurrentPosition = (e) => {
    if (e) {
      e.preventDefault();
    }
    const { showOutsideCountryError, coords } = this.context;
    this.searchplaceInput.value = '';
    this.changeNearMeButtonStatus('active');
    showFullScreenLoader();
    getLocationAccess()
      .then(
        async (pos) => {
          const userCoords = {
            lat: pos.coords.latitude,
            lng: pos.coords.longitude,
          };

          try {
            // If user and site country not same, don't process.
            const [userCountrySame] = await getUserLocation(userCoords);
            if (!userCountrySame) {
              removeFullScreenLoader();
              showOutsideCountryError(true);
              return;
            }
          } catch (error) {
            Drupal.logJavascriptError('clickncollect-checkUserCountry', error, GTM_CONSTANTS.CHECKOUT_ERRORS);
          }

          if (JSON.stringify(coords) === JSON.stringify(userCoords)) {
            removeFullScreenLoader();
            return;
          }

          this.fetchAvailableStores(userCoords, true);
        },
        () => {
          const defaultMapCenter = getDefaultMapCenter();
          if (JSON.stringify(coords) === JSON.stringify(defaultMapCenter)) {
            removeFullScreenLoader();
            return;
          }
          this.changeNearMeButtonStatus('in-active');
          this.fetchAvailableStores(defaultMapCenter, false);
        },
      )
      .catch((error) => {
        removeFullScreenLoader();
        Drupal.logJavascriptError('clickncollect-getCurrentPosition', error, GTM_CONSTANTS.CHECKOUT_ERRORS);
      });
    return false;
  };

  /**
   * Fetch available stores for given lat and lng.
   */
  fetchAvailableStores = (coords, locationAccess = null) => {
    const { updateCoordsAndStoreList, showOutsideCountryError, cartId } = this.context;
    showFullScreenLoader();
    // Create fetcher object to fetch stores.
    const args = {
      coords,
      cartId,
    };
    const storeFetcher = createFetcher(fetchClicknCollectStores);
    // Make api request.
    const list = storeFetcher.read(args);
    list
      .then((response) => {
        this.selectStoreButtonVisibility(false);
        if (typeof response.error === 'undefined') {
          updateCoordsAndStoreList(coords, response.data, locationAccess);
          this.showOpenMarker(response.data);
        } else {
          updateCoordsAndStoreList(coords, []);
        }
        showOutsideCountryError(false);
        removeFullScreenLoader();
      })
      .catch((error) => {
        removeFullScreenLoader();
        Drupal.logJavascriptError('clickncollect-fetchAvailableStores', error, GTM_CONSTANTS.CHECKOUT_ERRORS);
      });
  };

  toggleStoreView = (e, activeView) => {
    e.preventDefault();
    e.target.parentNode.childNodes.forEach((btn) => btn.classList.remove('active'));
    e.target.classList.add('active');
    if (activeView === 'map') {
      this.cncMapView.current.style.display = 'block';
      this.cncListView.current.style.display = 'none';
      this.refreshMap();
      const selectedStore = this.mapStoreList.current.querySelector('.selected');
      if (!selectedStore) {
        this.selectStoreButtonVisibility(false);
      } else {
        this.toggleFullScreen(true);
        this.openMarkerOfStore(selectedStore.dataset.storeCode);
      }
    } else {
      this.cncMapView.current.style.display = 'none';
      this.cncListView.current.style.display = 'block';
      const selectedStore = this.cncListView.current.querySelector('.selected');
      this.selectStoreButtonVisibility(!!selectedStore);
    }
    return false;
  };

  // View selected store on map.
  hightlightMapMarker = (makerIndex) => {
    const map = this.googleMap;
    // Make the marker by default open.
    google.maps.event.trigger(map.map.mapMarkers[makerIndex], 'click');
    if (map.map.mapMarkers[makerIndex] !== undefined) {
      const options = collectionPointsEnabled()
        ? { map_marker: { active: map.map.mapMarkers[makerIndex].icon } }
        : {};
      map.highlightIcon(map.map.mapMarkers[makerIndex], options);
    }
  }

  showOpenMarker = (storeList = null) => {
    const { selectedStore, storeList: contextStoreList } = this.context;
    const storeListArg = (!storeList) ? contextStoreList : storeList;
    if (!selectedStore) {
      this.selectStoreButtonVisibility(false);
      return;
    }
    this.openMarkerOfStore(selectedStore.code, storeListArg);
    this.closeAllInfoWindow();
  };

  showSelectedMarker = (storeList = null) => {
    const { selectedStore, storeList: contextStoreList } = this.context;
    const storeListArg = (!storeList) ? contextStoreList : storeList;
    if (!selectedStore) {
      return;
    }
    this.openMarkerOfStore(selectedStore.code, storeListArg, false);
    this.closeAllInfoWindow();
  };

  openMarkerOfStore = (storeCode, storeList = null, showInfoWindow = true) => {
    const { storeList: contextStoreList } = this.context;
    const storeListArg = (!storeList) ? contextStoreList : storeList;
    const index = _.findIndex(storeListArg, {
      code: storeCode,
    });
    this.selectStoreButtonVisibility(index >= 0);

    const self = this;
    // Wait for all markers to be placed in map before
    // Clicking on the marker.
    setTimeout(() => {
      this.hightlightMapMarker(index);
      if (showInfoWindow === false) {
        self.closeAllInfoWindow();
      }
    }, 100);
  };

  refreshMap = () => {
    const { map } = this.googleMap;
    // Adjust the map, when we trigger the map view.
    google.maps.event.trigger(map.googleMap, 'resize');
    if (map.mapMarkers.length > 0) {
      // Auto zoom.
      map.googleMap.fitBounds(map.googleMap.bounds);
      // Auto center.
      map.googleMap.panToBounds(map.googleMap.bounds);
    }
  };

  closeSelectedStorePanel = () => {
    const { selectedStore, storeList } = this.context;
    this.setState({
      openSelectedStore: false,
    });
    this.selectStoreButtonVisibility(true);
    this.openMarkerOfStore(selectedStore.code, storeList);
  };

  finalizeCurrentStore = (e) => {
    const selectedStore = this.cncListView.current.querySelector('.selected');
    this.finalizeStore(e, selectedStore.dataset.storeCode);
  };

  finalizeStore = (e, storeCode) => {
    const { storeList, updateSelectStore } = this.context;
    e.preventDefault();
    if (window.innerWidth < 768) {
      this.toggleFullScreen(false);
    }
    // Log a debug to know what is the store code being passed.
    logger.debug('Store code @storeCode selected by the user.', {
      '@storeCode': storeCode,
    });
    // Find the store object with the given store-code from the store list.
    const store = _.find(storeList, { code: storeCode });
    if (store === undefined) {
      logger.error('Unable to find store from list.', {
        storeCode,
        storeList,
      });
    } else if (store !== undefined && store.name === undefined) {
      logger.error('Unable to find store name in the store found in list', {
        store,
        storeList,
      });
    }

    dispatchCustomEvent('storeSelected', { store });
    updateSelectStore(store);
    this.setState({
      openSelectedStore: true,
    });
    this.selectStoreButtonVisibility(false);
    this.closeAllInfoWindow();
  };

  /**
   * Toggle map full screen.
   */
  toggleFullScreen = (fullscreen = null) => {
    if (fullscreen === true && isFullScreen()) {
      return;
    }

    if (isFullScreen() || fullscreen === false) {
      if (!isFullScreen()) {
        return;
      }
      const self = this;
      this.setState({
        mapFullScreen: false,
      });
      const selectedStore = this.mapStoreList.current.querySelector('.selected');
      if (selectedStore) {
        selectedStore.querySelector('.spc-map-list-close').click();
      }
      if (exitFullscreen()) {
        self.refreshMap();
      } else {
        Drupal.logJavascriptError(
          'clickncollect-toggleFullScreen',
          'Not able to exit full screen, click and collect map view.',
          GTM_CONSTANTS.CHECKOUT_ERRORS,
        );
      }

      if (!selectedStore) {
        this.selectStoreButtonVisibility(false);
      }
    } else {
      requestFullscreen(this.cncMapView.current);
      this.setState({
        mapFullScreen: true,
      });
    }
  };

  onMapStoreClose = (e, makerIndex) => {
    e.target.closest('.selected').classList.remove('selected');
    this.selectStoreButtonVisibility(false);
    this.refreshMap();
    this.toggleFullScreen();

    const map = this.googleMap;
    if (map.map.mapMarkers[makerIndex] !== undefined) {
      const options = collectionPointsEnabled()
        ? { map_marker: { inActive: map.map.mapMarkers[makerIndex].icon } }
        : {};
      map.resetIcon(map.map.mapMarkers[makerIndex], options);
    }
  }

  selectStoreButtonVisibility = (action) => {
    const selectStoreBtn = document.getElementsByClassName('spc-cnc-store-actions')[0];
    if (typeof selectStoreBtn !== 'undefined') {
      if (action === true) {
        selectStoreBtn.classList.add('show');
      } else {
        selectStoreBtn.classList.remove('show');
      }
    }
  }

  closeAllInfoWindow = () => {
    if (window.innerWidth < 768) {
      return;
    }
    this.googleMap.closeAllInfoWindow();
  }

  dismissErrorMessage = (e, type) => {
    const { showOutsideCountryError, updateLocationAccess } = this.context;
    const { changeNearMeButtonStatus } = this;
    e.target.parentNode.parentNode.classList.add('fadeOutUp');
    // Wait for warning message fade out animation.
    setTimeout(() => {
      if (type === 'outsidecountry') {
        showOutsideCountryError(false);
        changeNearMeButtonStatus('in-active');
      }
      if (type === 'locationAccessDenied') {
        updateLocationAccess(true);
      }
      this.resetListHeight();
    }, 200);
  }

  render() {
    const {
      coords,
      storeList,
      selectedStore,
      locationAccess,
      outsideCountryError,
    } = this.context;

    const {
      openSelectedStore,
      mapFullScreen,
    } = this.state;

    const { closeModal } = this.props;

    const mapView = (
      <ClicknCollectMap
        coords={coords}
        markers={storeList}
        openSelectedStore={openSelectedStore}
      />
    );

    return (
      <div className="spc-address-form">
        <DeviceView device="above-mobile">
          <div className="spc-address-form-map">{mapView}</div>
        </DeviceView>
        <div className="spc-cnc-address-form-sidebar">
          <div
            className="spc-cnc-stores-list-map"
            style={{ display: openSelectedStore ? 'none' : 'block' }}
          >
            <SectionTitle>{getStringMessage(getCncModalTitle())}</SectionTitle>
            <a className="close" onClick={closeModal}>
              &times;
            </a>
            <div className="spc-cnc-address-form-wrapper">
              {locationAccess === false
              && (
                <CheckoutMessage type="warning" context="click-n-collect-store-modal modal location-disable">
                  <span className="font-bold">{getStringMessage('location_access_denied')}</span>
                  <button type="button" onClick={(e) => this.dismissErrorMessage(e, 'locationAccessDenied')}>
                    {getStringMessage('dismiss')}
                  </button>
                </CheckoutMessage>
              )}
              {outsideCountryError === true
              && (
                <CheckoutMessage type="warning" context="click-n-collect-store-modal modal location-disable outside-country-error">
                  {parse(getStringMessage('location_outside_country_cnc'))}
                  <button type="button" onClick={(e) => this.dismissErrorMessage(e, 'outsidecountry')}>
                    {getStringMessage('dismiss')}
                  </button>
                </CheckoutMessage>
              )}
              <div className="spc-cnc-address-form-content">
                <SectionTitle>
                  {getStringMessage(getCncModalDescription())}
                </SectionTitle>
                <LocationSearchForm
                  ref={this.searchRef}
                  getCurrentPosition={this.getCurrentPosition}
                />
                <DeviceView device="mobile">
                  <ToggleButton toggleStoreView={this.toggleStoreView} />
                  <div
                    className="click-and-collect-map-view"
                    style={{ display: 'none' }}
                    ref={this.cncMapView}
                  >
                    {mapView}
                    <button className="spc-cnc-full-screen" type="button" onClick={() => this.toggleFullScreen()}>
                      <FullScreenSVG mapFullScreen={mapFullScreen} />
                    </button>
                    <div className="map-store-list" ref={this.mapStoreList}>
                      <StoreList
                        display="default"
                        storeList={storeList}
                        selected={selectedStore}
                        onStoreRadio={this.hightlightMapMarker}
                        onStoreFinalize={this.finalizeStore}
                        onStoreClose={this.onMapStoreClose}
                      />
                    </div>
                  </div>
                </DeviceView>
                <div id="click-and-collect-list-view" ref={this.cncListView}>
                  <StoreList
                    display={(window.innerWidth < 768) ? 'accordion' : 'teaser'}
                    storeList={storeList}
                    selected={selectedStore}
                    onStoreRadio={this.hightlightMapMarker}
                    onStoreFinalize={this.finalizeStore}
                  />
                </div>
              </div>
            </div>
          </div>
          <div className="spc-cnc-store-actions" data-selected-stored={openSelectedStore}>
            <button className="select-store" type="button" onClick={(e) => this.finalizeCurrentStore(e)}>
              {getStringMessage(getCncModalButtonText())}
            </button>
          </div>
          <SelectedStore
            store={selectedStore}
            open={openSelectedStore}
            closePanel={this.closeSelectedStorePanel}
          />
        </div>
      </div>
    );
  }
}

export default ClickCollect;
