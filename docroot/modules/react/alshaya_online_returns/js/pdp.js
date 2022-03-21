import React from 'react';
import ReactDOM from 'react-dom';
import OnlineReturnsPDP from './components/online-returns-pdp';
import isOnlineReturnsEnabled from '../../js/utilities/onlineReturnsHelper';

if (isOnlineReturnsEnabled()) {
  if ((window.innerWidth < 768)
    && document.querySelector('#online-returns-pdp-mobile')) {
    ReactDOM.render(
      <OnlineReturnsPDP />,
      document.querySelector('#online-returns-pdp-mobile'),
    );
  } else if (document.querySelector('#online-returns-pdp-desktop')) {
    ReactDOM.render(
      <OnlineReturnsPDP />,
      document.querySelector('#online-returns-pdp-desktop'),
    );
  }
}
