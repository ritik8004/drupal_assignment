import React from 'react';

import fetchAPIData from '../../../utilities/api/fetchApiData';
import StoreFinderMap from './components/store-finder-map';

const appointmentStoreFinder = drupalSettings.alshaya_appointment.store_finder;

export default class AppointmentStore extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      storeItems: '',
      initialCoords: {
        lat: appointmentStoreFinder.latitude,
        lng: appointmentStoreFinder.longitude,
      },
    };
  }

  componentDidMount() {
    const apiUrl = `${'/get/stores'
      + '?radius='}${appointmentStoreFinder.radius
    }&unit=${appointmentStoreFinder.unit
    }&max-locations=${appointmentStoreFinder.max_num_of_locations
    }&latitude=${appointmentStoreFinder.latitude
    }&longitude=${appointmentStoreFinder.longitude}`;

    const apiData = fetchAPIData(apiUrl);

    if (apiData instanceof Promise) {
      apiData.then((result) => {
        if (result.error === undefined && result.data !== undefined) {
          this.setState({
            storeItems: result.data,
          });
        }
      });
    }
  }

  convertKmToMile = (value) => {
    const realMiles = (value * 0.621371);
    const miles = Math.floor(realMiles);
    return miles;
  }

  handleStateChange = (storeItems) => {
    this.setState({
      storeItems,
    });
  }

  handleChange = (e) => {
    const value = e.target.type === 'checkbox' ? e.target.checked : e.target.value;
    this.setState({
      [e.target.name]: value,
    });
  }

  render() {
    const {
      storeItems,
      appointmentCategory,
      appointmentType,
      initialCoords,
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
              // disabled={!(appointmentCategory
              //   && appointmentType
              //   && appointmentCompanion
              //   && appointmentForYou
              //   && appointmentTermsConditions)}
              onClick={this.handleSubmit}
            >
              {Drupal.t('Display Stores Near Me')}
            </button>
            <span>
              -
              {Drupal.t('or')}
              {' '}
              -
            </span>
            <input
              type="text"
              className="input"
              onChange={this.handleChange}
              placeholder={Drupal.t('e.g. Salmiya')}
            />
          </div>
          <div className="store-map-wrapper">
            <div className="map-inner-wrapper">
              <StoreFinderMap
                coords={initialCoords}
                markers={storeItems}
                handleStateChange={this.handleStateChange}
                openSelectedStore={false}
              />
            </div>
            <div className="store-list-inner-wrapper" style={{ 'z-index': '1', position: 'absolute', top: '194%' }}>
              {storeItems && storeItems.map((v) => (
                <div className="store-list-item">
                  <input
                    type="radio"
                    value=""
                    name="storeItem"
                    onChange={this.handleChange}
                  />
                  <span className="store-name">{v.name}</span>
                  <span className="distance">
                    {v.distanceInMiles}
                    {Drupal.t('Miles')}
                  </span>
                </div>
              ))}
            </div>
          </div>
          <div className="appointment-store-buttons-wrapper">
            <button
              className="appointment-store-button back"
              type="button"
              onClick={this.handleSubmit}
            >
              {Drupal.t('BACK')}
            </button>
            <button
              className="appointment-store-button select-store"
              type="button"
              onClick={this.handleSubmit}
            >
              {Drupal.t('Select Store')}
            </button>
          </div>

        </div>
        <div className="appointment-details">
          <div className="appointment-details-header">
            {Drupal.t('You have chosen')}
          </div>
          <div className="appointment-details-body">
            <div className="appointment-details-item">
              <div className="appointment-details-item-header">
                <label>{Drupal.t('Appointment category')}</label>
                <button
                  className="appointment-details-button"
                  type="button"
                  onClick={this.handleSubmit}
                >
                  {Drupal.t('Edit')}
                </button>
              </div>
              <div className="appointment-details-item-body">
                {appointmentCategory && appointmentCategory.name}
              </div>
            </div>
            <div className="appointment-details-item">
              <div className="appointment-details-item-header">
                <label>{Drupal.t('Appointment type')}</label>
                <button
                  className="appointment-details-button"
                  type="button"
                  onClick={this.handleSubmit}
                >
                  {Drupal.t('Edit')}
                </button>
              </div>
              <div className="appointment-details-item-body">
                {appointmentType && appointmentType.name}
              </div>
            </div>
          </div>
        </div>
      </div>
    );
  }
}
