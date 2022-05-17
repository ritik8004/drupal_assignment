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
        // 'renderCongratulationPopup' will render congratulation
        // popup markup in mobile for logged in users.
        ReactDOM.render(
          <Header isMobileTab renderCongratulationPopup />,
          document.querySelector('#username-points-wrapper-mobile-menu'),
        );
      }
    } else {
      // Guest user.
      // 'renderCongratulationPopup' will render congratulation
      // popup in mobile for guest users. Please note that we are using this
      // variable to restrict congratulation popup markup rendering once on a
      // page else it will display multiple times in mobile.

      if (document.querySelector('#points-wrapper-mobile-menu')) {
        // For guest user mobile menu tab.
        ReactDOM.render(
          <Header isMobileTab renderCongratulationPopup />,
          document.querySelector('#points-wrapper-mobile-menu'),
        );
      }

      // Check if the sign in and register menu tab is available within the
      // secondary menu region. We need to ensure that Aura block display
      // at the end of this region in mobile.
      const secondaryMenu = document.getElementsByClassName('region__menu-secondary');
      if (secondaryMenu.length > 0) {
        const auraMobileHeader = document.createElement('div');
        auraMobileHeader.id = 'aura-mobile-header-signin-register';
        secondaryMenu[0].appendChild(auraMobileHeader);
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
    // 'renderCongratulationPopup' will render congratulation popup for desktop.
    ReactDOM.render(
      <Header isDesktop renderCongratulationPopup />,
      document.querySelector('#aura-header-modal'),
    );
  }
}
