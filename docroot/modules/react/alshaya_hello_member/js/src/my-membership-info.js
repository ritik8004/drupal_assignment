import React from 'react';
import ReactDOM from 'react-dom';
import MyMembership from './components/my-accounts/my-membership';

const querySelector = document.querySelector('#my-membership-info');
if (querySelector) {
  ReactDOM.render(
    <MyMembership />,
    querySelector,
  );
}
