import React from 'react';
import ReactDOM from 'react-dom';
import AuraPDP from './components/aura-pdp';
import isAuraEnabled from '../../js/utilities/helper';
import { isMobile } from '../../js/utilities/display';

let componentAttached = false;

// Note: This file is dynamically loaded in the library_info_alter hook of
// alshaya_rcs_product.module.
Drupal.behaviors.auraPdpRcsBehavior = {
  attach: function auraPdpRcs() {
    if (componentAttached
      || !isAuraEnabled()
      || document.getElementsByClassName('rcs-page rcs-loaded').length === 0
    ) {
      return;
    }

    if (isAuraEnabled()) {
      componentAttached = true;

      // For mobile view.
      if ((isMobile())
        && document.querySelector('#aura-pdp-mobile')) {
        ReactDOM.render(
          <AuraPDP mode="main" />,
          document.querySelector('#aura-pdp-mobile'),
        );
      } else if (document.querySelector('#aura-pdp')) {
        // For tablet & desktop view.
        ReactDOM.render(
          <AuraPDP mode="main" />,
          document.querySelector('#aura-pdp'),
        );
      }
    }
  },
};
