import React from 'react';
import ReactDOM from 'react-dom';
import Header from './components/header';
import { getStorageInfo } from '../../js/utilities/storage';
import { getAuraLocalStorageKey } from './utilities/aura_utils';
import { getUserDetails } from './utilities/helper';

const localStorageValues = getStorageInfo(getAuraLocalStorageKey());

// Upto desktop, show Aura inside hamburger menu.
if (window.innerWidth < 1024) {
  if (getUserDetails().id) {
    ReactDOM.render(
      <Header loggedInMobile />,
      document.querySelector('#username-points-wrapper-mobile-menu'),
    );
  } else if (localStorageValues !== null) {
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
    <Header isDesktop />,
    document.querySelector('#aura-header-modal'),
  );
}
