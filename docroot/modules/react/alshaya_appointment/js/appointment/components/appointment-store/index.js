import React from 'react';
import fetchAPIData from '../../../utilities/api/fetchApiData';
import StoreList from './components/store-list';
import StoreFinderGoogleMap from './components/store-finder-map';
import { getInputValue, getLocationAccess } from '../../../utilities/helper';
import { setStorageInfo, getStorageInfo } from '../../../utilities/storage';

const {
  radius, unit, max_num_of_locations: locCount, latitude, longitude,
} = drupalSettings.alshaya_appointment.store_finder;
const initialCoords = {
  lat: latitude,
  lng: longitude,
};
const localStorageValues = getStorageInfo();
let storeListForSearch;

export default class AppointmentStore extends React.Component {
  constructor(props) {
    super(props);
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
    const apiUrl = `/get/stores?radius=${radius}&unit=${unit}&max-locations=${locCount}&latitude=${latitude}&longitude=${longitude}`;
    this.fetchStores(apiUrl);
  }

  fetchStores = (apiUrl) => {
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

          const apiUrl = `/get/stores?radius=${radius}&unit=${unit}&max-locations=${locCount}&latitude=${coords.lat}&longitude=${coords.lng}`;
          this.fetchStores(apiUrl);
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

  searchHandler = (event) => {
    const { storeItems } = this.state;
    let filterItems = '';
    const filteredStoreItems = [];
    storeListForSearch = storeListForSearch || storeItems;

    const searchQuery = event.target.value.toLowerCase();
    filterItems = storeListForSearch && Object.entries(storeListForSearch).filter(([k, el]) => {
      const match = el.name.toLowerCase().indexOf(searchQuery) !== -1;
      if (match) {
        filteredStoreItems.push(el);
      }
      return match;
    });

    this.setState((prevState) => ({
      ...prevState,
      storeItems: filteredStoreItems,
    }));
  }

  render() {
    const {
      storeItems,
      selectedStoreItem,
    } = this.state;

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
              className="input"
              placeholder={Drupal.t('e.g. Salmiya')}
              onChange={this.searchHandler}
            />
          </div>
          <div className="store-map-wrapper">
            <div className="map-inner-wrapper">
              <StoreFinderGoogleMap
                coords={initialCoords}
                markers={storeItems}
                handleStateChange={this.handleStateChange}
                openSelectedStore={false}
              />
            </div>
            <StoreList
              storeList={storeItems}
              coords={initialCoords}
              handleStateChange={this.handleStateChange}
              handleStoreSelect={this.handleChange}
              activeItem={selectedStoreItem && JSON.parse(selectedStoreItem).locationExternalId}
            />
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
