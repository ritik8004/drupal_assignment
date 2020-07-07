import React from 'react';

const AppointmentToggleButton = ({ toggleStoreView }) => (
  <div className="toggle-store-view">
    <div className="toggle-buttons-wrapper">
      <button
        className="stores-list-view active"
        type="button"
        onClick={(e) => toggleStoreView(e, 'list')}
      >
        {Drupal.t('List View')}
      </button>
      <button
        className="stores-map-view"
        type="button"
        onClick={(e) => toggleStoreView(e, 'map')}
      >
        {Drupal.t('Map View')}
      </button>
    </div>
  </div>
);

export default React.memo(AppointmentToggleButton);
