import React from 'react';
import ReactDOM from 'react-dom';
import Header from './components/header';
import { getUserDetails } from './utilities/helper';
import isAuraEnabled from '../../js/utilities/helper';

if (isAuraEnabled()) {
  // Upto desktop header.
  if (window.innerWidth < 1024) {
    // Logged in user.
    if (getUserDetails().id) {
      if (document.querySelector('#username-points-wrapper-mobile-menu')) {
        // For logged in user mobile menu tab.
        ReactDOM.render(
          <Header isMobileTab />,
          document.querySelector('#username-points-wrapper-mobile-menu'),
        );
      }
    } else {
      // Guest user.
      if (document.querySelector('#points-wrapper-mobile-menu')) {
        // For guest user mobile menu tab.
        ReactDOM.render(
          <Header isMobileTab />,
          document.querySelector('#points-wrapper-mobile-menu'),
        );
      }

      if (document.querySelector('#aura-mobile-header-signin-register')) {
        // For guest user sign in/register tab.
        ReactDOM.render(
          <Header isNotExpandable />,
          document.querySelector('#aura-mobile-header-signin-register'),
        );
      }
    }

    // Both logged in and guest.
    if (document.querySelector('#aura-mobile-header-shop')) {
      // For mobile menu shop tab.
      ReactDOM.render(
        <Header isHeaderShop />,
        document.querySelector('#aura-mobile-header-shop'),
      );
    }
  } else if (document.querySelector('#aura-header-modal')) {
    // Desktop header.
    ReactDOM.render(
      <Header isDesktop />,
      document.querySelector('#aura-header-modal'),
    );
  }
}
