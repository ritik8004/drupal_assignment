import React from 'react';
import { fetchAPIData } from '../../../utilities/api/fetchApiData';
import StoreList from './components/store-list';
import { getInputValue, getLocationAccess, getDistanceBetweenCoords } from '../../../utilities/helper';
import { setStorageInfo, getStorageInfo } from '../../../utilities/storage';
import ConditionalView from '../../../common/components/conditional-view';
import AppointmentToggleButton from './components/AppointmentToggleButton';
import { removeFullScreenLoader, showFullScreenLoader } from '../../../utilities/appointment-util';
import Loading from '../../../utilities/loading';
import dispatchCustomEvent from '../../../utilities/events';
import FullScreenSVG from '../../../svg-component/full-screen-svg';
import DeviceView from '../../../common/components/device-view';
import ToggleButton from  './components/store-map/ToggleButton';

const StoreMap = React.lazy(async () => {
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
        ...localStorageValues,
        refCoords: { lat: latitude, lng: longitude },
        openSelectedStore: openSelectedStore || false,
        mapFullScreen: false,
      };
    }
  }

  componentDidMount() {
    const { latitude, longitude } = drupalSettings.alshaya_appointment.store_finder;
    this.fetchStores(latitude, longitude);
    document.addEventListener('fetchStoreSuccess', this.initiatePlaceAutocomplete);
  }

  initiatePlaceAutocomplete = () => {
    this.autocomplete = new google.maps.places.Autocomplete(this.autocompleteInput.current,
      { types: ['geocode'] });

    console.log(this.autocomplete);

    this.autocomplete.addListener('place_changed', this.handlePlaceChanged);
  }

  fetchStores = (lat, lng) => {
    const {
      radius, unit, max_num_of_locations: locCount,
    } = drupalSettings.alshaya_appointment.store_finder;

    // Show loader.
    showFullScreenLoader();

    const apiUrl = `/get/stores?radius=${radius}&unit=${unit}&max-locations=${locCount}&latitude=${lat}&longitude=${lng}`;
    const apiData = fetchAPIData(apiUrl);

    if (apiData instanceof Promise) {
      apiData.then((result) => {
        if (result.error === undefined && result.data !== undefined) {
          window.fetchStore = 'finished';
          const { refCoords } = this.state;
          const storeItems = getDistanceBetweenCoords(result.data, refCoords);
          this.setState((prevState) => ({
            ...prevState,
            storeItems,
          }));

          dispatchCustomEvent('fetchStoreSuccess', true);

          // Remove loader.
          removeFullScreenLoader();
        } else {
          window.fetchStore = 'failed';
        }
      });
    }
  }

  handlePlaceChanged = () => {
    const place = this.autocomplete.getPlace();
    if (typeof place !== 'undefined' && typeof place.geometry !== 'undefined') {
      const coords = {
        lat: place.geometry.location.lat(),
        lng: place.geometry.location.lng(),
      };
      this.setState((prevState) => ({
        ...prevState,
        refCoords: coords,
      }));
      this.fetchStores(coords.lat, coords.lng);
    }
  }

  handleDisplayStoresNearMe = () => {
    getLocationAccess()
      .then(
        async (pos) => {
          const coords = {
            lat: pos.coords.latitude,
            lng: pos.coords.longitude,
          };
          this.setState((prevState) => ({
            ...prevState,
            refCoords: coords,
          }));

          this.fetchStores(coords.lat, coords.lng);
        },
      );
  }

  handleStateChange = (storeItems) => {
    const { refCoords } = this.state;
    const storeItemsWithDistance = getDistanceBetweenCoords(storeItems, refCoords);
    this.setState({
      storeItems: storeItemsWithDistance,
    });
  }

  handleChange = (e) => {
    const value = getInputValue(e);
    this.setState({
      [e.target.name]: value,
    });
  }

  handleBack = (step) => {
    const { handleBack } = this.props;
    handleBack(step);
  }

  handleSubmit = () => {
    setStorageInfo(this.state);
    const { handleSubmit } = this.props;
    handleSubmit();
  }

  // removeClassFromStoreList = (className) => {
  //   // Add Class expand to the currently opened li.
  //   const tempStoreListNodes = document.querySelectorAll('#appointment-map-store-list-view label.select-store');
  //   const tempStoreList = [].slice.call(tempStoreListNodes);
  //   // Remove class expand from all.
  //   tempStoreList.forEach((storeElement) => {
  //     storeElement.classList.remove(className);
  //   });
  // };

  // addClassToStoreItem = (element, className) => {
  //   // Close already opened item.
  //   if (element.classList.contains(className)) {
  //     if (className === 'expand') {
  //       element.classList.remove(className);
  //     }
  //     return;
  //   }
  //   // Add Class expand to the currently opened li.
  //   this.removeClassFromStoreList(className);
  //   element.classList.add(className);
  // };

  // expandStoreItem = (e) => {
  //   this.addClassToStoreItem(e.target.parentElement.parentElement, 'expand');
  // };

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
        selectedStore.querySelector('.appointment-map-list-close').click();
      }
      if (exitFullscreen()) {
        self.refreshMap();
      } else {
        Drupal.logJavascriptError('appointment-toggleFullScreen', 'Not able to exit full screen, appointment map view.');
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

  selectStoreButtonVisibility = (action) => {
    const selectStoreBtn = document.getElementsByClassName('appointment-store-actions')[0];
    if (action === true) {
      selectStoreBtn.classList.add('show');
    } else {
      selectStoreBtn.classList.remove('show');
    }
  }

  openMarkerOfStore = (storeCode, storeList = null, showInfoWindow = true) => {
    const { storeList: contextStoreList } = this.context;
    const storeListArg = (!storeList) ? contextStoreList : storeList;
    const index = _findIndex(storeListArg, {
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

  // View selected store on map.
  hightlightMapMarker = (makerIndex) => {
    const map = window.appointmentMap;
    // Make the marker by default open.
    google.maps.event.trigger(map.map.mapMarkers[makerIndex], 'click');
    if (map.map.mapMarkers[makerIndex] !== undefined) {
      map.highlightIcon(map.map.mapMarkers[makerIndex]);
    }
  }

  finalizeStore = (e, storeCode) => {
    const { storeList, updateSelectStore } = this.context;
    e.preventDefault();
    if (window.innerWidth < 768) {
      this.toggleFullScreen(false);
    }
    // Find the store object with the given store-code from the store list.
    const store = _find(storeList, { code: storeCode });
    dispatchCustomEvent('storeSelected', { store });
    updateSelectStore(store);
    this.setState({
      openSelectedStore: true,
    });
    this.selectStoreButtonVisibility(false);
    this.closeAllInfoWindow();
  };

  closeAllInfoWindow = () => {
    if (window.innerWidth < 768) {
      return;
    }
    window.appointmentMap.closeAllInfoWindow();
  }

  toggleStoreView = (e, activeView) => {
  console.log(activeView);

    e.preventDefault();
    e.target.parentNode.childNodes.forEach((btn) => btn.classList.remove('active'));
    e.target.classList.add('active');
    if (activeView === 'map') {
      this.appMapView.current.style.display = 'block';
      this.appListView.current.style.display = 'none';
      this.refreshMap();
      const selectedStore = this.mapStoreList.current.querySelector('.selected');
console.log(selectedStore);

      if (!selectedStore) {
        this.selectStoreButtonVisibility(false);
      } else {
        this.toggleFullScreen(true);
        this.openMarkerOfStore(selectedStore.dataset.storeCode);
      }
    } else {
      this.appMapView.current.style.display = 'none';
      this.appListView.current.style.display = 'block';
      const selectedStore = this.appMapView.current.querySelector('.selected');
      this.selectStoreButtonVisibility(!!selectedStore);
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
    const {
      storeItems,
      selectedStoreItem,
      openSelectedStore,
      mapFullScreen
    } = this.state;
    const { latitude, longitude } = drupalSettings.alshaya_appointment.store_finder;
    const mapView = (
      <StoreMap
        coords={{
          lat: latitude,
          lng: longitude,
        }}
        markers={storeItems}
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

          <div className="store-finder-wrapper">
            <div className="store-finder-container">
              <ConditionalView condition={window.innerWidth > 1023}>
                <button
                  className="appointment-type-button store-finder-button"
                  type="button"
                  onClick={this.handleDisplayStoresNearMe}
                >
                  {Drupal.t('Display Stores Near Me')}
                </button>
                <span>
                  {` - ${Drupal.t('Or')} - `}
                </span>
              </ConditionalView>
              <label>
                {Drupal.t('Find your closest location')}
              </label>
            </div>
            <div className="store-finder-input">
              <input
                type="text"
                id="autocomplete"
                className="input"
                ref={this.autocompleteInput}
                placeholder={Drupal.t('enter a location')}
              />
            </div>
            <ConditionalView condition={window.innerWidth < 1024}>
              <button
                className="appointment-store-near-me"
                id="edit-near-me"
                type="button"
                onClick={this.handleDisplayStoresNearMe}
              >
                {Drupal.t('Display Stores Near Me')}
              </button>
            </ConditionalView>
          </div>

          <React.Suspense fallback={<Loading />}>
            <div className="store-map-wrapper">
              <div className="map-inner-wrapper">
                {mapView}
              </div>

              {/* <div id="appointment-map-store-list-view" className="appointment-map-store-list-view" ref={this.appListView}>
                { storeItems && (
                <StoreList
                  storeList={storeItems}
                  handleStateChange={this.handleStateChange}
                  handleStoreSelect={this.handleChange}
                  activeItem={selectedStoreItem && JSON.parse(selectedStoreItem).locationExternalId}
                  display={(window.innerWidth < 1024) ? 'accordion' : 'teaser'}
                  onStoreExpand={this.expandStoreItem}
                />
                )}
              </div> */}

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
                        storeList={storeItems}
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
                    storeList={storeItems}
                    selected={selectedStoreItem}
                    onStoreRadio={this.hightlightMapMarker}
                    onStoreFinalize={this.finalizeStore}
                  />
                </div>
            </div>
          </React.Suspense>

          <div className="appointment-store-buttons-wrapper">
            <button
              className="appointment-store-button appointment-type-button back"
              type="button"
              onClick={() => this.handleBack('appointment-type')}
            >
              {Drupal.t('Back')}
            </button>
            <button
              className="appointment-store-button appointment-type-button select-store appointment-store-actions"
              type="button"
              disabled={!(selectedStoreItem)}
              onClick={this.handleSubmit}
            >
              {Drupal.t('Select Store')}
            </button>
          </div>

        </div>
      </div>
    );
  }
}
