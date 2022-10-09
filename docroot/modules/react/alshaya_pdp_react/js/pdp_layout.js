import React from 'react';
import ReactDOM from 'react-dom';
import { hasValue } from '../../js/utilities/conditionsUtility';
import PdpLayout from './pdp-layout/components/pdp-layout';

// Update drupalSettings if V3 enabled.
if (hasValue(drupalSettings.alshayaRcs)) {
  window.alshayaRenderPdpMagV2 = function renderPdpMagV2(productInfo,
    configurableCombinations) {
    drupalSettings.productInfo = productInfo;
    drupalSettings.configurableCombinations = configurableCombinations;
    drupalSettings.showNewPdpDescContainer = drupalSettings.alshayaRcs.show_new_pdp_desc_container;

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
