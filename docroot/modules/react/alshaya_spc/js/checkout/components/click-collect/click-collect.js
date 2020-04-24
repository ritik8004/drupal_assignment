import React from 'react';
import _find from 'lodash/find';
import _findIndex from 'lodash/findIndex';
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

class ClickCollect extends React.Component {
  static contextType = ClicknCollectContext;

  constructor(props) {
    super(props);
    this.searchRef = React.createRef();
    this.cncListView = React.createRef();
    this.cncMapView = React.createRef();

    this.mapStoreList = React.createRef();
    this.autocomplete = null;
    this.searchplaceInput = null;
    this.nearMeBtn = null;

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
      outsideCountryError,
    } = this.context;
    updateModal(true);

    if (!this.autocomplete && !!this.searchRef && !!this.searchRef.current) {
      this.searchplaceInput = this.searchRef.current.getElementsByTagName('input').item(0);
      this.autocomplete = new window.google.maps.places.Autocomplete(
        this.searchplaceInput,
        {
          types: [],
          componentRestrictions: { country: window.drupalSettings.country_code },
        },
      );

      this.autocomplete.addListener(
        'place_changed',
        this.placesAutocompleteHandler,
      );
      this.nearMeBtn = this.searchRef.current.getElementsByTagName('button').item(0);
    }

    if (coords !== null && storeList.length === 0) {
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
  }

  componentDidUpdate() {
    const { outsideCountryError } = this.context;
    if (outsideCountryError) {
      smoothScrollTo('.spc-cnc-address-form-sidebar .spc-checkout-section-title');
    }
  }

  componentWillUnmount() {
    document.removeEventListener('markerClick', this.mapMarkerClick);
  }

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
    this.nearMeBtn.classList.remove('active');
    if (typeof place !== 'undefined' && typeof place.geometry !== 'undefined') {
      this.fetchAvailableStores({
        lat: place.geometry.location.lat(),
        lng: place.geometry.location.lng(),
      });
    }
  };

  /**
   * Get current location coordinates.
   */
  getCurrentPosition = (e) => {
    if (e) {
      e.preventDefault();
    }
    const { showOutsideCountryError } = this.context;
    this.searchplaceInput.value = '';
    this.nearMeBtn.classList.add('active');
    showFullScreenLoader();
    getLocationAccess()
      .then(
        async (pos) => {
          const coords = {
            lat: pos.coords.latitude,
            lng: pos.coords.longitude,
          };
          try {
            const [userCountrySame] = await getUserLocation(coords);
            // If user and site country not same, don;t process.
            if (!userCountrySame) {
              removeFullScreenLoader();
              // Show error message.
              showOutsideCountryError(true);
              return;
            }
          } catch (error) {
            Drupal.logJavascriptError('clickncollect-checkUserCountry', error);
          }

          this.fetchAvailableStores({
            lat: pos.coords.latitude,
            lng: pos.coords.longitude,
          }, true);
        },
        () => {
          removeFullScreenLoader();
          this.nearMeBtn.classList.remove('active');
          this.fetchAvailableStores(getDefaultMapCenter(), false);
        },
      )
      .catch((error) => {
        removeFullScreenLoader();
        Drupal.logJavascriptError('clickncollect-getCurrentPosition', error);
      });
    return false;
  };

  /**
   * Fetch available stores for given lat and lng.
   */
  fetchAvailableStores = (coords, locationAccess = null) => {
    const { updateCoordsAndStoreList, showOutsideCountryError } = this.context;
    showFullScreenLoader();
    // Create fetcher object to fetch stores.
    const storeFetcher = createFetcher(fetchClicknCollectStores);
    // Make api request.
    const list = storeFetcher.read(coords);
    list
      .then((response) => {
        this.selectStoreButtonVisibility(false);
        if (typeof response.error === 'undefined') {
          updateCoordsAndStoreList(coords, response.data, locationAccess);
          this.showOpenMarker(response);
        } else {
          updateCoordsAndStoreList(coords, []);
        }
        showOutsideCountryError(false);
        removeFullScreenLoader();
      })
      .catch((error) => {
        Drupal.logJavascriptError('clickncollect-fetchAvailableStores', error);
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
    const map = window.spcMap;
    // Make the marker by default open.
    google.maps.event.trigger(map.map.mapMarkers[makerIndex], 'click');
    if (map.map.mapMarkers[makerIndex] !== undefined) {
      map.highlightIcon(map.map.mapMarkers[makerIndex]);
    }
  }

  showOpenMarker = (storeList = null) => {
    const { selectedStore, storeList: contextStoreList } = this.context;
    const storeListArg = (!storeList) ? contextStoreList : storeList;

    if (!selectedStore) {
      this.selectStoreButtonVisibility(false);
      return;
    }
    this.selectStoreButtonVisibility(false);
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
    const index = _findIndex(storeListArg, {
      code: storeCode,
    });

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
    const { map } = window.spcMap;
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

    // Find the store object with the given store-code from the store list.
    const store = _find(storeList, { code: storeCode });
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
        Drupal.logJavascriptError('clickncollect-toggleFullScreen', 'Not able to exit full screen, click and collect map view.');
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
    e.target.parentElement.parentElement.classList.remove('selected');
    this.selectStoreButtonVisibility(false);
    this.refreshMap();
    this.toggleFullScreen();

    const map = window.spcMap;
    if (map.map.mapMarkers[makerIndex] !== undefined) {
      map.resetIcon(map.map.mapMarkers[makerIndex]);
    }
  }

  selectStoreButtonVisibility = (action) => {
    const selectStoreBtn = document.getElementsByClassName('spc-cnc-store-actions')[0];
    if (action === true) {
      selectStoreBtn.classList.add('show');
    } else {
      selectStoreBtn.classList.remove('show');
    }
  }

  closeAllInfoWindow = () => {
    if (window.innerWidth < 768) {
      return;
    }
    window.spcMap.closeAllInfoWindow();
  }

  render() {
    const {
      coords,
      storeList,
      selectedStore,
      locationAccess,
      updateLocationAccess,
      outsideCountryError,
      showOutsideCountryError,
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
            <SectionTitle>{getStringMessage('collection_store')}</SectionTitle>
            <a className="close" onClick={closeModal}>
              &times;
            </a>
            <div className="spc-cnc-address-form-wrapper">
              {locationAccess === false
              && (
                <CheckoutMessage type="warning" context="click-n-collect-store-modal modal location-disable">
                  <span className="font-bold">{getStringMessage('location_access_denied')}</span>
                  <a href="#" onClick={() => updateLocationAccess(true)}>{getStringMessage('dismiss')}</a>
                </CheckoutMessage>
              )}
              {outsideCountryError === true
              && (
                <CheckoutMessage type="warning" context="click-n-collect-store-modal modal location-disable">
                  <span className="font-bold">{parse(getStringMessage('location_outside_country_cnc'))}</span>
                  <a href="#" onClick={() => showOutsideCountryError(false)}>{getStringMessage('dismiss')}</a>
                </CheckoutMessage>
              )}
              <div className="spc-cnc-address-form-content">
                <SectionTitle>
                  {getStringMessage('find_your_nearest_store')}
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
              {getStringMessage('select_this_store')}
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
