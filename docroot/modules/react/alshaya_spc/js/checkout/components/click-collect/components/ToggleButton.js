import React from 'react';
import getStringMessage from '../../../../utilities/strings';

const ToggleButton = ({ toggleStoreView }) => (
  <div className="toggle-store-view">
    <div className="toggle-buttons-wrapper">
      <button
        className="stores-list-view active"
        type="button"
        onClick={(e) => toggleStoreView(e, 'list')}
      >
        {getStringMessage('cnc_list_view')}
      </button>
      <button
        className="stores-map-view"
        type="button"
        onClick={(e) => toggleStoreView(e, 'map')}
      >
        {getStringMessage('cnc_map_view')}
      </button>
    </div>
  </div>
);

export default React.memo(ToggleButton);
