import React from 'react';
import ReactDOM from 'react-dom';
import MyAccount from './components/my-accounts';

const querySelector = document.querySelector('#my-accounts-hello-member');
if (querySelector) {
  ReactDOM.render(
    <MyAccount />,
    querySelector,
  );
}
