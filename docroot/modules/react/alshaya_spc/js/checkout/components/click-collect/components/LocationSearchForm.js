import React from 'react';

const LocationSearchForm = React.forwardRef((props, ref) => (
  <div className="spc-cnc-location-search-wrapper" ref={ref}>
    <div className="spc-cnc-store-search-form-item">
      <input
        className="form-search"
        type="search"
        id="edit-store-location"
        name="store_location"
        placeholder={drupalSettings.map.placeholder}
        autoComplete="off"
      />
    </div>
    <button
      className="cc-near-me"
      id="edit-near-me"
      onClick={(e) => props.getCurrentPosition(e)}
    >
      {Drupal.t('Near me')}
    </button>
  </div>
));

export default LocationSearchForm;
