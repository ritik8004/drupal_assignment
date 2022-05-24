import React from 'react';
import ReactDOM from 'react-dom';
import { StoreClickCollectList } from './alshaya-store-click-collect-list';

let componentRendered = false;

Drupal.behaviors.alshayaGeolocationCncRcsBehavior = {
  attach: function alshayaGeolocationCnCRcs() {
    if (jQuery('.entity--type-node').not('[data-sku *= "#"]').length === 0) {
      return;
    }
    if (componentRendered) {
      return;
    }

    componentRendered = true;
    ReactDOM.render(
      <StoreClickCollectList />,
      document.getElementById('pdp-store-click-collect-list'),
    );
  },
};
