import React from 'react';
import ReactDOM from 'react-dom';
import OnlineReturnsPDP from './pdp/components/online-returns-pdp';
import isOnlineReturnsEnabled from '../../js/utilities/onlineReturnsHelper';

if (isOnlineReturnsEnabled() && document.querySelector('#online-returns-pdp')) {
  ReactDOM.render(
    <OnlineReturnsPDP />,
    document.querySelector('#online-returns-pdp'),
  );
}
