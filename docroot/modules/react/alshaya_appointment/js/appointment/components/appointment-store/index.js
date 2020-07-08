import React from 'react';
import {
  GoogleApiWrapper,
} from 'google-maps-react';
import { fetchAPIData } from '../../../utilities/api/fetchApiData';
import StoreList from './components/store-list';
import StoreFinderGoogleMap from './components/store-finder-map';
import { getInputValue, getLocationAccess } from '../../../utilities/helper';
import { setStorageInfo, getStorageInfo } from '../../../utilities/storage';
import ConditionalView from '../../../common/components/conditional-view';
import AppointmentToggleButton from './components/AppointmentToggleButton';
import { removeFullScreenLoader, showFullScreenLoader } from '../../../utilities/appointment-util';

export class AppointmentStore extends React.Component {
  constructor(props) {
    super(props);
    this.autocompleteInput = React.createRef();
    this.autocomplete = null;
    this.appListView = React.createRef();
    this.appMapView = React.createRef();

    const localStorageValues = getStorageInfo();

    if (localStorageValues) {
      this.state = {
        ...localStorageValues,
      };
    } else {
      this.state = {
        storeItems: '',
        selectedStoreItem: '',
      };
    }
  }

  componentDidMount() {
    const { latitude, longitude } = drupalSettings.alshaya_appointment.store_finder;
    this.fetchStores(latitude, longitude);

    this.autocomplete = new google.maps.places.Autocomplete(this.autocompleteInput.current,
      { types: ['geocode'] });
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
          this.setState((prevState) => ({
            ...prevState,
            storeItems: result.data,
          }));

          // Remove loader.
          removeFullScreenLoader();
        }
      });
    }
  }

  handlePlaceChanged = () => {
    const place = this.autocomplete.getPlace();
    if (typeof place !== 'undefined' && typeof place.geometry !== 'undefined') {
      this.fetchStores(place.geometry.location.lat(), place.geometry.location.lng());
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
            userCoords: coords,
          }));

          this.fetchStores(coords.lat, coords.lng);
        },
      );
  }

  handleStateChange = (storeItems) => {
    this.setState({
      storeItems,
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

  removeClassFromStoreList = (className) => {
    // Add Class expand to the currently opened li.
    const tempStoreListNodes = document.querySelectorAll('#appointment-map-store-list-view label.select-store');
    const tempStoreList = [].slice.call(tempStoreListNodes);
    // Remove class expand from all.
    tempStoreList.forEach((storeElement) => {
      storeElement.classList.remove(className);
    });
  };

  addClassToStoreItem = (element, className) => {
    // Close already opened item.
    if (element.classList.contains(className)) {
      if (className === 'expand') {
        element.classList.remove(className);
      }
      return;
    }
    // Add Class expand to the currently opened li.
    this.removeClassFromStoreList(className);
    element.classList.add(className);
  };

  expandStoreItem = (e) => {
    this.addClassToStoreItem(e.target.parentElement.parentElement, 'expand');
  };

  toggleStoreView = (e, activeView) => {
    e.preventDefault();
    e.target.parentNode.childNodes.forEach((btn) => btn.classList.remove('active'));
    e.target.classList.add('active');
    if (activeView === 'map') {
      this.appMapView.current.style.display = 'block';
      this.appListView.current.style.display = 'none';
    } else {
      this.appMapView.current.style.display = 'none';
      this.appListView.current.style.display = 'block';
    }
    return false;
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

  render() {
    const {
      storeItems,
      selectedStoreItem,
    } = this.state;

    const { google } = this.props;

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
          <div className="store-map-wrapper">
            <ConditionalView condition={window.innerWidth > 1023}>
              <div className="map-inner-wrapper">
                <StoreFinderGoogleMap
                  markers={storeItems}
                  handleStateChange={this.handleStateChange}
                  openSelectedStore={false}
                  google={google}
                  handleStoreSelect={this.handleChange}
                />
              </div>
            </ConditionalView>
            <ConditionalView condition={window.innerWidth < 1024}>
              <AppointmentToggleButton toggleStoreView={this.toggleStoreView} />
              <div
                className="appointment-map-view"
                style={{ display: 'none' }}
                ref={this.appMapView}
              >
                <StoreFinderGoogleMap
                  markers={storeItems}
                  handleStateChange={this.handleStateChange}
                  openSelectedStore={false}
                  google={google}
                  handleStoreSelect={this.handleChange}
                />
              </div>
            </ConditionalView>
            <div id="appointment-map-store-list-view" className="appointment-map-store-list-view" ref={this.appListView}>
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
            </div>
          </div>
          <div className="appointment-store-buttons-wrapper">
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

export default GoogleApiWrapper({
  apiKey: drupalSettings.alshaya_appointment.google_map_api_key,
  libraries: ['places', 'geometry'],
})(AppointmentStore);
