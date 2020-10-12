import React from 'react';
import ReactDOM from 'react-dom';
import MyAccount from './components/my-account';

if (window.innerWidth < 768) {
  ReactDOM.render(
    <MyAccount />,
    document.querySelector('#block-alshayamyaccountlinks #my-accounts-aura-mobile'),
  );
} else {
  ReactDOM.render(
    <MyAccount />,
    document.querySelector('#my-accounts-aura'),
  );
}
