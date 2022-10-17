import React from 'react';
import ReactDOM from 'react-dom';
import { hasValue } from '../../js/utilities/conditionsUtility';
import PdpLayout from './pdp-layout/components/pdp-layout';

window.alshayaRenderPdpMagV2 = function renderPdpMagV2(productInfo, configurableCombinations) {
  ReactDOM.render(
    <PdpLayout
      productInfo={productInfo}
      configurableCombinations={configurableCombinations}
    />,
    document.getElementById('pdp-layout'),
  );
};

if (hasValue(drupalSettings)) {
  const { productInfo, configurableCombinations } = drupalSettings;
  if (hasValue(productInfo) && hasValue(configurableCombinations)) {
    window.alshayaRenderPdpMagV2(productInfo, configurableCombinations);
  }
}
