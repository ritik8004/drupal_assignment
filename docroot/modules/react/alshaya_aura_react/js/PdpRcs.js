import React from 'react';
import ReactDOM from 'react-dom';
import AuraPDP from './components/aura-pdp';
import isAuraEnabled from '../../js/utilities/helper';

let componentAttached = false;

// Note: This file is dynamically loaded in the library_info_alter hook of
// alshaya_rcs_product.module.
Drupal.behaviors.auraPdpRcsBehavior = {
  attach: function auraPdpRcs() {
    const pageLoaded = document.querySelector('.rcs-page.rcs-loaded');
    if (!componentAttached
        && pageLoaded
        && isAuraEnabled()
        && document.querySelector('#aura-pdp')
    ) {
      componentAttached = true;
      ReactDOM.render(
        <AuraPDP mode="main" />,
        document.querySelector('#aura-pdp'),
      );
    }
  },
};
