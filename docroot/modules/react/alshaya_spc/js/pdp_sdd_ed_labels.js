import React from 'react';
import ReactDOM from 'react-dom';
import PdpSddEd from '../../js/utilities/components/pdp-sdd-ed';

/**
 * Show PDP labels on default and magazine layout.
 */
const renderPdpDeliveryLabels = () => {
  if (document.getElementById('sdd-ed-labels') !== null) {
    ReactDOM.render(
      <PdpSddEd />,
      document.getElementById('sdd-ed-labels'),
    );
  }
};

renderPdpDeliveryLabels();
