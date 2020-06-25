import React from 'react';

const PdpClickCollectSearch = React.forwardRef((props, ref) => (
  <div className="location-field-wrapper" ref={ref}>
    <div className="location-field">
      <input
        className="form-search"
        type="search"
        id="edit-store-location"
        name="store_location"
        placeholder={drupalSettings.clickNCollect.cncFormPlaceholder}
        autoComplete="off"
      />
    </div>
  </div>
));

export default PdpClickCollectSearch;
