import React from 'react';
import ReactDOM from 'react-dom';
import { hasValue } from '../../js/utilities/conditionsUtility';
import PdpLayout from './pdp-layout/components/pdp-layout';

// Update drupalSettings if V3 enabled.
if (hasValue(drupalSettings.alshayaRcs)) {
  window.alshayaRenderPdpMagV2 = function renderPdpMagV2(productInfo, configurableCombinations) {
    // Update product details coming from qraphql for the current product.
    drupalSettings.productInfo = productInfo || '';
    drupalSettings.configurableCombinations = configurableCombinations || '';

    ReactDOM.render(
      <PdpLayout />,
      document.getElementById('pdp-layout'),
    );
  };
} else {
  ReactDOM.render(
    <PdpLayout />,
    document.getElementById('pdp-layout'),
  );
}
