import React from 'react';
import getStringMessage from '../../../../../../../js/utilities/strings';

const ToggleButton = ({ toggleStoreView }) => (
  <div className="toggle-store-view">
    <div className="toggle-buttons-wrapper">
      <button
        className="stores-list-view active"
        type="button"
        onClick={(e) => toggleStoreView(e, 'list')}
      >
        {getStringMessage('list_view_label')}
      </button>
      <button
        className="stores-map-view"
        type="button"
        onClick={(e) => toggleStoreView(e, 'map')}
      >
        {getStringMessage('map_view_label')}
      </button>
    </div>
  </div>
);

export default React.memo(ToggleButton);
