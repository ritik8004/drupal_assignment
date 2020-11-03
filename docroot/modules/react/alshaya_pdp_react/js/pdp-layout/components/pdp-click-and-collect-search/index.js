import React from 'react';
import ClickCollectSearchSVG from '../../../svg-component/cc-search-svg';

const PdpClickCollectSearch = React.forwardRef((props, ref) => (
  <div className="location-field-wrapper fadeInUp" ref={ref}>
    <div className="location-field">
      <span className="magv2-card-icon-svg magv2-click-n-collect-search-svg">
        <ClickCollectSearchSVG />
      </span>
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
