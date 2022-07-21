import React from 'react';
import ReactDOM from 'react-dom';
import MyPointsHistory from './components/my-accounts/my-points-history';

const querySelector = document.querySelector('#my-accounts-points-history');
if (querySelector) {
  ReactDOM.render(
    <MyPointsHistory />,
    querySelector,
  );
}
