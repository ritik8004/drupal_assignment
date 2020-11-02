import React from 'react';
import ReactDOM from 'react-dom';
import Header from './components/header';
import { getUserDetails } from './utilities/helper';

// Upto desktop, show Aura inside hamburger menu.
if (window.innerWidth < 1024) {
  // Logged in user.
  if (getUserDetails().id) {
    ReactDOM.render(
      <Header isMobileTab />,
      document.querySelector('#username-points-wrapper-mobile-menu'),
    );
  } else {
    // Guest user.
    if (document.querySelector('#points-wrapper-mobile-menu')) {
      ReactDOM.render(
        <Header isMobileTab />,
        document.querySelector('#points-wrapper-mobile-menu'),
      );
    }

    if (document.querySelector('#aura-mobile-header-signin-register')) {
      ReactDOM.render(
        <Header isNotExpandable />,
        document.querySelector('#aura-mobile-header-signin-register'),
      );
    }

    if (document.querySelector('#aura-mobile-header-shop')) {
      ReactDOM.render(
        <Header />,
        document.querySelector('#aura-mobile-header-shop'),
      );
    }
  }
} else if (document.querySelector('#aura-header-modal')) {
  // Desktop header.
  ReactDOM.render(
    <Header isDesktop />,
    document.querySelector('#aura-header-modal'),
  );
}
