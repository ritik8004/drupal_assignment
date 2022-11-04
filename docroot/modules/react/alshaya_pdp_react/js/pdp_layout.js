import React from 'react';
import ReactDOM from 'react-dom';
import { hasValue } from '../../js/utilities/conditionsUtility';
import PdpLayout from './pdp-layout/components/pdp-layout';

window.alshayaRenderPdpMagV2 = function renderPdpMagV2(productInfo, configurableCombinations) {
  const elementPdpLayout = document.getElementById('pdp-layout');
  ReactDOM.render(
    <PdpLayout
      productInfo={productInfo}
      configurableCombinations={configurableCombinations}
    />,
    elementPdpLayout,
  );

  if (Drupal) {
    Drupal.attachBehaviors(elementPdpLayout);
  }
};

if (hasValue(drupalSettings)) {
  const { productInfo, configurableCombinations } = drupalSettings;
  if (hasValue(productInfo)) {
    window.alshayaRenderPdpMagV2(productInfo, configurableCombinations);
  }
}
