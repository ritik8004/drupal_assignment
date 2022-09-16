import React from 'react';
import ReactDOM from 'react-dom';
import PdpSddEd from '../../js/utilities/components/pdp-sdd-ed';
import { hasValue } from '../../js/utilities/conditionsUtility';

/**
 * Show PDP labels on default and magazine layout.
 */
const renderPdpDeliveryLabels = () => {
  if (document.getElementById('sdd-ed-labels-desktop') !== null) {
    ReactDOM.render(
      <PdpSddEd />,
      document.getElementById('sdd-ed-labels-desktop'),
    );
  }

  if (document.getElementById('sdd-ed-labels-mobile') !== null) {
    ReactDOM.render(
      <PdpSddEd />,
      document.getElementById('sdd-ed-labels-mobile'),
    );
  }
};

// Set pdp labels for mobile view on magazine layout
// after product zoom gallery is loaded in order
// to have the wrapper loaded in dom.
if (hasValue(drupalSettings.alshayaRcs)) {
  document.addEventListener('onSkuBaseFormLoad', renderPdpDeliveryLabels, false);
} else {
  document.addEventListener('productGalleryLoaded', renderPdpDeliveryLabels, false);
}


// Set pdp labels on load for default layout.
renderPdpDeliveryLabels();
