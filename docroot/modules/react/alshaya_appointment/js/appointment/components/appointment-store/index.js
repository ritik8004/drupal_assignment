import React from 'react';
import {
  GoogleApiWrapper,
} from 'google-maps-react';
import { fetchAPIData } from '../../../utilities/api/fetchApiData';
import StoreList from './components/store-list';
import StoreFinderGoogleMap from './components/store-finder-map';
import { getInputValue, getLocationAccess } from '../../../utilities/helper';
import { setStorageInfo, getStorageInfo } from '../../../utilities/storage';

export class AppointmentStore extends React.Component {
  constructor(props) {
    super(props);
    this.autocompleteInput = React.createRef();
    this.autocomplete = null;

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

    const apiUrl = `/get/stores?radius=${radius}&unit=${unit}&max-locations=${locCount}&latitude=${lat}&longitude=${lng}`;
    const apiData = fetchAPIData(apiUrl);

    if (apiData instanceof Promise) {
      apiData.then((result) => {
        if (result.error === undefined && result.data !== undefined) {
          this.setState((prevState) => ({
            ...prevState,
            storeItems: result.data,
          }));
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

  render() {
    const {
      storeItems,
      selectedStoreItem,
    } = this.state;

    const { google } = this.props;

    return (
      <div className="appointment-store-wrapper">
        <div className="appointment-store-inner-wrapper">
          <div className="store-header">
            {Drupal.t("Select a store that's convenient for you")}
            *
          </div>
          <div className="store-finder-wrapper">
            <button
              className="appointment-type-button"
              type="button"
              onClick={this.handleDisplayStoresNearMe}
            >
              {Drupal.t('Display Stores Near Me')}
            </button>
            <span>
              {` - ${Drupal.t('or')} - `}
            </span>
            <label>
              {Drupal.t('Find your closest location')}
            </label>
            <input
              type="text"
              id="autocomplete"
              className="input"
              ref={this.autocompleteInput}
              placeholder={Drupal.t('e.g. Salmiya')}
            />
          </div>
          <div className="store-map-wrapper">
            <div className="map-inner-wrapper">
              <StoreFinderGoogleMap
                markers={storeItems}
                handleStateChange={this.handleStateChange}
                openSelectedStore={false}
                google={google}
              />
            </div>
            { storeItems && (
            <StoreList
              storeList={storeItems}
              handleStateChange={this.handleStateChange}
              handleStoreSelect={this.handleChange}
              activeItem={selectedStoreItem && JSON.parse(selectedStoreItem).locationExternalId}
            />
            )}
          </div>
          <div className="appointment-store-buttons-wrapper">
            <button
              className="appointment-store-button back"
              type="button"
              onClick={() => this.handleBack('appointment-type')}
            >
              {Drupal.t('BACK')}
            </button>
            <button
              className="appointment-store-button select-store"
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
