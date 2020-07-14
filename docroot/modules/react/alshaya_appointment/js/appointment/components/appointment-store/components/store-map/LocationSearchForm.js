import React from 'react';
import ConditionalView from '../../../../../common/components/conditional-view';

const LocationSearchForm = React.forwardRef((props, ref) => (
  <div className="store-finder-wrapper appointment-location-search-wrapper" ref={ref}>
    <div className="store-finder-container">

      <ConditionalView condition={window.innerWidth > 1023}>
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
      </ConditionalView>
      <label>
        {Drupal.t('Find your closest location')}
      </label>
    </div>

    <div className="store-finder-input">
      <input
        type="search"
        id="autocomplete"
        className="input"
        name="store_location"
        placeholder={drupalSettings.alshaya_appointment.store_finder.placeholder}
      />
    </div>

    <ConditionalView condition={window.innerWidth < 1024}>
      <button
        className="appointment-store-near-me"
        id="edit-near-me"
        type="button"
        onClick={(e) => props.getCurrentPosition(e)}
      >
        {Drupal.t('Display Stores Near Me')}
      </button>
    </ConditionalView>

  </div>
));

export default LocationSearchForm;
