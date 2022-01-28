import React from 'react';
import ReactDOM from 'react-dom';
import AuraPDP from './components/aura-pdp';
import isAuraEnabled from '../../js/utilities/helper';

let componentAttached = false;

Drupal.behaviors.auraPdpV2Behavior = {
  attach: function auraPdpV2() {
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
