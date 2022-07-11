import React from 'react';
import ReactDOM from 'react-dom';
import Header from './components/header';
import { getUserDetails } from './utilities/helper';
import isAuraEnabled from '../../js/utilities/helper';
import AuraCongratulationsModal from '../../alshaya_spc/js/aura-loyalty/components/aura-congratulations';

/**
 * Renders the AURA header component.
 */
Drupal.displayAuraHeader = () => {
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
      ReactDOM.render(
        <Header isDesktop />,
        document.querySelector('#aura-header-modal'),
      );
    }

    // Create a wrapper element for aura congratulation popup. We will append this
    // dynamic element at the end of the `<body>` tag and render the
    // `AuraCongratulationsModal` component markup within this. This modal will
    // be used globally for all aura signup and signin popups from header, cart
    // and checkout pages.
    const bodyElem = document.querySelector('body');
    const auraCongratulationPopupWrapper = document.createElement('div');
    auraCongratulationPopupWrapper.id = 'aura-congratulation-popup-modal';
    bodyElem.appendChild(auraCongratulationPopupWrapper);
    ReactDOM.render(
      <AuraCongratulationsModal />,
      document.querySelector('#aura-congratulation-popup-modal'),
    );
  }
};
