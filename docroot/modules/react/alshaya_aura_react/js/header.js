import React from 'react';
import ReactDOM from 'react-dom';
import Header from './components/header';
import { getStorageInfo } from '../../js/utilities/storage';
import { getAuraLocalStorageKey } from './utilities/aura_utils';

const localStorageValues = getStorageInfo(getAuraLocalStorageKey());

if (window.innerWidth < 768) {
  if (localStorageValues !== null) {
    if (document.querySelector('#aura-mobile-header-signin-register')) {
      ReactDOM.render(
        <Header isNotExpandable />,
        document.querySelector('#aura-mobile-header-signin-register'),
      );
    }
  } else if (document.querySelector('#aura-mobile-header-shop')) {
    ReactDOM.render(
      <Header />,
      document.querySelector('#aura-mobile-header-shop'),
    );
  }
} else if (document.querySelector('#aura-header-modal')) {
  ReactDOM.render(
    <Header />,
    document.querySelector('#aura-header-modal'),
  );
}
