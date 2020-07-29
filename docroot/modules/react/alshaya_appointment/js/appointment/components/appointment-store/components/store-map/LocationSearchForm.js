import React from 'react';
import DeviceView from '../../../../../common/components/device-view';

const LocationSearchForm = React.forwardRef((props, ref) => (
  <div
    className="store-finder-wrapper appointment-location-search-wrapper fadeInUp"
    style={{ animationDelay: '0.6s' }}
    ref={ref}
  >
    <DeviceView device="above-mobile">
      <div className="store-finder-container">
        <button
          className="appointment-type-button store-finder-button"
          id="edit-near-me"
          type="button"
          onClick={(e) => props.getCurrentPosition(e)}
        >
          {Drupal.t('Display Stores Near Me')}
        </button>
        <span>
          {` - ${Drupal.t('Or')} - `}
        </span>
        <label>
          {Drupal.t('Find your closest location')}
        </label>
      </div>
    </DeviceView>

    <DeviceView device="mobile">
      <label>
        {Drupal.t('Find your closest location')}
      </label>
    </DeviceView>

    <div className="store-finder-input">
      <input
        type="text"
        id="autocomplete"
        className="input"
        name="store_location"
        placeholder={drupalSettings.alshaya_appointment.store_finder.placeholder}
      />
    </div>

    <DeviceView device="mobile">
      <button
        className="appointment-store-near-me"
        id="edit-near-me"
        type="button"
        onClick={(e) => props.getCurrentPosition(e)}
      >
        {Drupal.t('Display Stores Near Me')}
      </button>
    </DeviceView>
  </div>
));

export default LocationSearchForm;
