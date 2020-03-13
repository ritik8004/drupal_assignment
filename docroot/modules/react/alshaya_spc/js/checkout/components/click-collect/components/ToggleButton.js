import React from 'react';

const ToggleButton = ({ toggleStoreView }) => (
  <div className="toggle-store-view">
    <div className="toggle-buttons-wrapper">
      <button
        className="stores-list-view active"
        onClick={(e) => toggleStoreView(e, 'list')}
      >
        {Drupal.t('List view')}
      </button>
      <button
        className="stores-map-view"
        onClick={(e) => toggleStoreView(e, 'map')}
      >
        {Drupal.t('Map view')}
      </button>
    </div>
  </div>
);

export default React.memo(ToggleButton);
