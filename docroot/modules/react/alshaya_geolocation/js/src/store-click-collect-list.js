import React from 'react';
import ReactDOM from 'react-dom';
import { StoreClickCollectList } from './alshaya-store-click-collect-list';

let componentRendered = false;

(function storeClickCollectListMain(drupalSettings) {
  Drupal.behaviors.alshayaGeolocationCncRcsBehavior = {
    attach: function alshayaGeolocationCnCRcs() {
      if (drupalSettings.storeLabels.state !== 'enabled') {
        return;
      }

      const node = jQuery('.entity--type-node').not('[data-sku *= "#"]');
      if (!node.length || (node.length && componentRendered)) {
        return;
      }

      componentRendered = true;
      // Click & Collect section will render only if it is enabled.
      ReactDOM.render(
        <StoreClickCollectList />,
        document.getElementById('pdp-store-click-collect-list'),
      );
    },
  };
}(drupalSettings));
