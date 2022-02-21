import React from 'react';
import ReactDOM from 'react-dom';
import AuraPDP from './components/aura-pdp';
import isAuraEnabled from '../../js/utilities/helper';

let componentAttached = false;

// Note: This file is dynamically loaded in the library_info_alter hook of
// alshaya_rcs_product.module.
Drupal.behaviors.auraPdpRcsBehavior = {
  attach: function auraPdpRcs() {
    const pageLoaded = document.getElementsByClassName('rcs-page rcs-loaded').length;
    if (!componentAttached
        && pageLoaded
        && isAuraEnabled()
    ) {
      const auraPdp = document.getElementById('aura-pdp');
      if (auraPdp) {
        componentAttached = true;
        ReactDOM.render(
          <AuraPDP mode="main" />,
          auraPdp,
        );
      }
    }
  },
};
