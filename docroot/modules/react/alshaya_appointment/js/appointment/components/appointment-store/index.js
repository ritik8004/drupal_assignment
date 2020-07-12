import React from 'react';
import _find from 'lodash/find';
import _findIndex from 'lodash/findIndex';
import { fetchAPIData } from '../../../utilities/api/fetchApiData';
import StoreList from './components/store-list';
import { getLocationAccess, getDistanceBetweenCoords } from '../../../utilities/helper';
import { setStorageInfo, getStorageInfo } from '../../../utilities/storage';
import { removeFullScreenLoader, showFullScreenLoader } from '../../../utilities/appointment-util';
import Loading from '../../../utilities/loading';
import dispatchCustomEvent from '../../../utilities/events';
import FullScreenSVG from '../../../svg-component/full-screen-svg';
import DeviceView from '../../../common/components/device-view';
import ToggleButton from './components/store-map/ToggleButton';
import { getDefaultMapCenter, getUserLocation } from '../../../utilities/map/map_utils';
import LocationSearchForm from './components/store-map/LocationSearchForm';
import Gmap from '../../../utilities/map/GMap';
import {
  requestFullscreen,
  isFullScreen,
  exitFullscreen,
} from '../../../utilities/map/fullScreen';

const StoreMap = React.lazy(async () => {
  const localStorageValues = getStorageInfo();
  window.fetchStore = (localStorageValues.storeList && localStorageValues.storeList.length !== 0) ? 'finished' : window.fetchStore;

  // Wait for fetchstore request to finish, before
  // We show select store with map.
  await new Promise((resolve) => {
    const interval = setInterval(() => {
      if (window.fetchStore === 'finished' && Drupal.alshayaAppointment.maps_api_loading === false) {
        clearInterval(interval);
        resolve();
      }
    }, 500);
  });
  return import('./components/store-map');
});
window.fetchStore = 'idle';

export default class AppointmentStore extends React.Component {
  constructor(props) {
    super(props);
    // Global map object.
    this.googleMap = new Gmap();
    window.appointmentMap = this.googleMap;
    this.searchRef = React.createRef();
    this.autocompleteInput = React.createRef();
    this.autocomplete = null;
    this.appListView = React.createRef();
    this.appMapView = React.createRef();
    this.mapStoreList = React.createRef();
    this.searchplaceInput = null;
    this.nearMeBtn = null;
    const { openSelectedStore } = this.props;
    const localStorageValues = getStorageInfo();

    if (localStorageValues) {
      const { latitude, longitude } = drupalSettings.alshaya_appointment.store_finder;
      this.state = {
        storeList: [],
        refCoords: { lat: latitude, lng: longitude },
        openSelectedStore: openSelectedStore || false,
        mapFullScreen: false,
        selectedStoreItem: '',
        locationAccess: true,
        outsideCountryError: false,
        ...localStorageValues,
      };
    }
  }

  componentDidMount() {
    // For autocomplete text field.
    const {
      refCoords, storeList, openSelectedStore, selectedStoreItem,
    } = this.state;
    document.addEventListener('fetchStoreSuccess', this.initiatePlaceAutocomplete);
    if (storeList.length !== 0) {
      dispatchCustomEvent('fetchStoreSuccess', true);
    } else if (refCoords !== null && storeList.length === 0) {
      this.fetchStores(refCoords);
    }

    // Ask for location access when we don't have any coords.
    if (refCoords !== null && openSelectedStore) {
      this.showSelectedMarker();
    }
    // Show "select this store" button, if a store is selected.
    if (selectedStoreItem && openSelectedStore === false) {
      this.showOpenMarker();
      this.selectStoreButtonVisibility(true);
    }
    // On marker click.
    document.addEventListener('markerClick', this.mapMarkerClick);
  }

  componentWillUnmount() {
    document.removeEventListener('markerClick', this.mapMarkerClick);
  }

  initiatePlaceAutocomplete = () => {
    if (!this.autocomplete && !!this.searchRef && !!this.searchRef.current) {
      this.searchplaceInput = this.searchRef.current.getElementsByTagName('input').item(0);
      this.autocomplete = new window.google.maps.places.Autocomplete(
        this.searchplaceInput,
        {
          types: [],
          componentRestrictions:
            { country: window.drupalSettings.alshaya_appointment.country_code },
        },
      );
      this.autocomplete.addListener(
        'place_changed',
        this.placesAutocompleteHandler,
      );
      this.nearMeBtn = this.searchRef.current.getElementsByTagName('button').item(0);
    }
  }

  fetchStores = (coords, locationAccess = null) => {
    const {
      radius, unit, max_num_of_locations: locCount,
    } = drupalSettings.alshaya_appointment.store_finder;

    // Show loader.
    showFullScreenLoader();

    const apiUrl = `/get/stores?radius=${radius}&unit=${unit}&max-locations=${locCount}&latitude=${coords.lat}&longitude=${coords.lng}`;
    const apiData = fetchAPIData(apiUrl);

    if (apiData instanceof Promise) {
      apiData.then((result) => {
        this.selectStoreButtonVisibility(false);

        if (result.error === undefined && result.data !== undefined) {
          window.fetchStore = 'finished';
          dispatchCustomEvent('fetchStoreSuccess', true);

          const storeList = getDistanceBetweenCoords(result.data, coords);
          this.updateCoordsAndStoreList(coords, storeList, locationAccess);
          this.showOpenMarker(result.data);
          this.showOutsideCountryError(false);

          // Remove loader.
          removeFullScreenLoader();
        } else {
          window.fetchStore = 'failed';
          this.updateCoordsAndStoreList(coords, []);
        }
      })
        .catch((error) => {
          removeFullScreenLoader();
          Drupal.logJavascriptError('appointment-fetchAvailableStores', error);
        });
    }
  }

  handleBack = (step) => {
    const { handleBack } = this.props;
    handleBack(step);
  }

  updateSelectedStore = (store) => {
    this.setState({
      selectedStoreItem: store,
    });
    // Save selected store in localStorage.
    const localStorage = getStorageInfo();
    localStorage.selectedStoreItem = store;
    setStorageInfo(localStorage);

    // Update step.
    const { handleSubmit } = this.props;
    handleSubmit();
  }

  updateCoordsAndStoreList = (refCoords, storeList, accessStatus = null) => {
    this.setState((prevState) => ({
      ...prevState,
      refCoords,
      storeList,
      locationAccess: (accessStatus !== null) ? accessStatus : prevState.locationAccess,
    }));
    // Update localStorage.
    const localStorage = getStorageInfo();
    localStorage.refCoords = refCoords;
    localStorage.storeList = storeList;
    setStorageInfo(localStorage);
  }

  showOutsideCountryError = (status) => {
    this.setState((prevState) => ({
      ...prevState,
      outsideCountryError: status,
    }));
  }

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
      const selectedStoreItem = this.mapStoreList.current.querySelector('.selected');
      if (selectedStoreItem) {
        selectedStoreItem.querySelector('.appointment-map-list-close').click();
      }
      if (exitFullscreen()) {
        self.refreshMap();
      } else {
        Drupal.logJavascriptError('appointment-toggleFullScreen', 'Not able to exit full screen, appointment map view.');
      }
      if (!selectedStoreItem) {
        this.selectStoreButtonVisibility(false);
      }
    } else {
      requestFullscreen(this.appMapView.current);
      this.setState({
        mapFullScreen: true,
      });
    }
  };

  showSelectedMarker = (storeList = null) => {
    const { selectedStoreItem, storeList: contextStoreList } = this.state;
    const storeListArg = (!storeList) ? contextStoreList : storeList;
    if (!selectedStoreItem) {
      return;
    }
    this.openMarkerOfStore(selectedStoreItem.locationExternalId, storeListArg, false);
    this.closeAllInfoWindow();
  };

  mapMarkerClick = (e) => {
    const index = e.detail.markerSettings.zIndex - 1;
    const allStores = this.appListView.current.querySelectorAll('.select-store');
    this.removeClassForAll(allStores, 'selected');

    this.appListView.current.querySelector(`[data-index="${index}"]`).classList.add('selected');
    this.selectStoreButtonVisibility(true);
    if (window.innerWidth < 768 && this.appMapView.current !== null && this.appMapView.current.style.display === 'block') {
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
      const coords = {
        lat: place.geometry.location.lat(),
        lng: place.geometry.location.lng(),
      };
      this.setState((prevState) => ({
        ...prevState,
        refCoords: coords,
      }));
      this.fetchStores(coords);
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
    const { refCoords } = this.state;
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
              this.showOutsideCountryError(true);
              return;
            }
          } catch (error) {
            Drupal.logJavascriptError('appointment-select-store-checkUserCountry', error);
          }
          if (JSON.stringify(refCoords) === JSON.stringify(userCoords)) {
            removeFullScreenLoader();
            return;
          }
          this.fetchStores(userCoords, true);
        },
        () => {
          const defaultMapCenter = getDefaultMapCenter();
          if (JSON.stringify(refCoords) === JSON.stringify(defaultMapCenter)) {
            removeFullScreenLoader();
            return;
          }
          this.changeNearMeButtonStatus('in-active');
          this.fetchStores(defaultMapCenter, false);
        },
      )
      .catch((error) => {
        removeFullScreenLoader();
        Drupal.logJavascriptError('appointment-select-store-getCurrentPosition', error);
      });
    return false;
  };

  showOpenMarker = (storeList = null) => {
    const { selectedStoreItem, storeList: contextStoreList } = this.state;
    const storeListArg = (!storeList) ? contextStoreList : storeList;
    if (!selectedStoreItem) {
      this.selectStoreButtonVisibility(false);
      return;
    }
    this.openMarkerOfStore(selectedStoreItem.locationExternalId, storeListArg);
    this.closeAllInfoWindow();
  };

  selectStoreButtonVisibility = (action) => {
    const selectStoreBtn = document.getElementsByClassName('appointment-store-actions')[0];
    if (action === true) {
      selectStoreBtn.classList.add('show');
    } else {
      selectStoreBtn.classList.remove('show');
    }
  }

  openMarkerOfStore = (storeCode, storeList = null, showInfoWindow = true) => {
    const { storeList: contextStoreList } = this.state;
    const storeListArg = (!storeList) ? contextStoreList : storeList;
    const index = _findIndex(storeListArg, {
      locationExternalId: storeCode,
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

  // View selected store on map.
  hightlightMapMarker = (makerIndex) => {
    const map = window.appointmentMap;
    // Make the marker by default open.
    google.maps.event.trigger(map.map.mapMarkers[makerIndex], 'click');
    if (map.map.mapMarkers[makerIndex] !== undefined) {
      map.highlightIcon(map.map.mapMarkers[makerIndex]);
    }
  }

  closeAllInfoWindow = () => {
    if (window.innerWidth < 768) {
      return;
    }
    window.appointmentMap.closeAllInfoWindow();
  }

  finalizeCurrentStore = (e) => {
    const selectedStoreItem = this.appListView.current.querySelector('.selected');
    this.finalizeStore(e, selectedStoreItem.dataset.storeCode);
  };

  finalizeStore = (e, storeCode) => {
    const { storeList } = this.state;
    e.preventDefault();
    if (window.innerWidth < 768) {
      this.toggleFullScreen(false);
    }
    // Find the store object with the given store-code from the store list.
    const store = _find(storeList, { locationExternalId: storeCode });
    dispatchCustomEvent('storeSelected', { store });
    this.updateSelectedStore(store);
    this.setState({
      openSelectedStore: true,
    });
    this.selectStoreButtonVisibility(false);
    this.closeAllInfoWindow();
  };

  onMapStoreClose = (e, makerIndex) => {
    e.target.closest('li.select-store').classList.remove('selected');
    this.selectStoreButtonVisibility(false);
    this.refreshMap();
    this.toggleFullScreen();

    const map = window.appointmentMap;
    if (map.map.mapMarkers[makerIndex] !== undefined) {
      map.resetIcon(map.map.mapMarkers[makerIndex]);
    }
  }

  toggleStoreView = (e, activeView) => {
    e.preventDefault();
    e.target.parentNode.childNodes.forEach((btn) => btn.classList.remove('active'));
    e.target.classList.add('active');
    if (activeView === 'map') {
      this.appMapView.current.style.display = 'block';
      this.appListView.current.style.display = 'none';
      this.refreshMap();
      const selectedStoreItem = this.mapStoreList.current.querySelector('.selected');

      if (!selectedStoreItem) {
        this.selectStoreButtonVisibility(false);
      } else {
        this.toggleFullScreen(true);
        this.openMarkerOfStore(selectedStoreItem.dataset.storeCode);
      }
    } else {
      this.appMapView.current.style.display = 'none';
      this.appListView.current.style.display = 'block';
      const selectedStoreItem = this.appMapView.current.querySelector('.selected');
      this.selectStoreButtonVisibility(!!selectedStoreItem);
    }
    return false;
  };

  refreshMap = () => {
    const { map } = window.appointmentMap;
    // Adjust the map, when we trigger the map view.
    google.maps.event.trigger(map.googleMap, 'resize');
    if (map.mapMarkers.length > 0) {
      // Auto zoom.
      map.googleMap.fitBounds(map.googleMap.bounds);
      // Auto center.
      map.googleMap.panToBounds(map.googleMap.bounds);
    }
  };

  render() {
    const { latitude, longitude } = drupalSettings.alshaya_appointment.store_finder;
    const {
      storeList,
      selectedStoreItem,
      openSelectedStore,
      mapFullScreen,
    } = this.state;
    const mapView = (
      <StoreMap
        coords={{
          lat: latitude,
          lng: longitude,
        }}
        markers={storeList}
        openSelectedStore={openSelectedStore}
      />
    );

    return (
      <div className="appointment-store-wrapper">
        <div className="appointment-store-inner-wrapper">
          <div className="store-header appointment-subtitle">
            {Drupal.t("Select a store that's convenient for you")}
            *
          </div>
          <LocationSearchForm
            ref={this.searchRef}
            getCurrentPosition={this.getCurrentPosition}
          />
          <React.Suspense fallback={<Loading loadingMessage={Drupal.t('Loading Map')} />}>
            <div className="store-map-wrapper">
              <DeviceView device="above-mobile">
                <div className="map-inner-wrapper">{mapView}</div>
              </DeviceView>
              <DeviceView device="mobile">
                <ToggleButton toggleStoreView={this.toggleStoreView} />
                <div
                  className="appointment-map-view"
                  style={{ display: 'none' }}
                  ref={this.appMapView}
                >
                  {mapView}
                  <button className="appointment-full-screen" type="button" onClick={() => this.toggleFullScreen()}>
                    <FullScreenSVG mapFullScreen={mapFullScreen} />
                  </button>
                  <div className="map-store-list" ref={this.mapStoreList}>
                    <StoreList
                      display="default"
                      storeList={storeList}
                      selected={selectedStoreItem}
                      onStoreRadio={this.hightlightMapMarker}
                      onStoreFinalize={this.finalizeStore}
                      onStoreClose={this.onMapStoreClose}
                    />
                  </div>
                </div>
              </DeviceView>
              <div
                id="appointment-map-store-list-view"
                className="appointment-map-store-list-view"
                ref={this.appListView}
              >
                <StoreList
                  display={(window.innerWidth < 768) ? 'accordion' : 'teaser'}
                  storeList={storeList}
                  selected={selectedStoreItem}
                  onStoreRadio={this.hightlightMapMarker}
                  onStoreFinalize={this.finalizeStore}
                />
              </div>
            </div>
          </React.Suspense>
          <div className="appointment-store-actions appointment-store-buttons-wrapper" data-selected-stored={openSelectedStore}>
            <button
              className="appointment-store-button appointment-type-button back"
              type="button"
              onClick={() => this.handleBack('appointment-type')}
            >
              {Drupal.t('Back')}
            </button>
            <button
              className="appointment-store-button appointment-type-button select-store"
              type="button"
              onClick={(e) => this.finalizeCurrentStore(e)}
            >
              {Drupal.t('Select Store')}
            </button>
          </div>
        </div>
      </div>
    );
  }
}
