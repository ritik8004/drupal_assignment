import React from 'react';
import ReactDOM from 'react-dom';
import AuraPDP from './components/aura-pdp';
import isAuraEnabled from '../../js/utilities/helper';
import { isMobile } from '../../js/utilities/display';

if (isAuraEnabled()) {
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
