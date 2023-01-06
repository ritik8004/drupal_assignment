import React from 'react';
import ReactDOM from 'react-dom';
import OnlineReturnsPDP from './pdp/components/online-returns-pdp';
import { isOnlineReturnsEnabled } from '../../js/utilities/onlineReturnsHelper';
import { isMobile } from '../../js/utilities/display';

if (isOnlineReturnsEnabled()) {
  // For mobile view.
  if ((isMobile())
    && document.querySelector('#online-returns-pdp-mobile')) {
    ReactDOM.render(
      <OnlineReturnsPDP
        eligibleForReturn
      />,
      document.querySelector('#online-returns-pdp-mobile'),
    );
  } else if (document.querySelector('#online-returns-pdp-desktop')) {
    // For tablet & desktop view.
    ReactDOM.render(
      <OnlineReturnsPDP
        eligibleForReturn
      />,
      document.querySelector('#online-returns-pdp-desktop'),
    );
  }
}
