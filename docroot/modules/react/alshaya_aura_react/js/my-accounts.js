import React from 'react';
import ReactDOM from 'react-dom';
import MyAccount from './components/my-account';
import isAuraEnabled from '../../js/utilities/helper';

if (isAuraEnabled()) {
  if ((window.innerWidth < 768)
    && document.querySelector('#block-alshayamyaccountlinks #my-accounts-aura-mobile')) {
    ReactDOM.render(
      <MyAccount />,
      document.querySelector('#block-alshayamyaccountlinks #my-accounts-aura-mobile'),
    );
  } else if (document.querySelector('#my-accounts-aura')) {
    ReactDOM.render(
      <MyAccount />,
      document.querySelector('#my-accounts-aura'),
    );
  }
}
