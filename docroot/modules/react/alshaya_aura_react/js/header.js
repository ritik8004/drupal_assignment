import React from 'react';
import ReactDOM from 'react-dom';
import Header from './components/header';

if (window.innerWidth < 768) {
  if (document.querySelector('#aura-mobile-header-signin-register')) {
    ReactDOM.render(
      <Header isMobile />,
      document.querySelector('#aura-mobile-header-signin-register'),
    );
  }
} else if (document.querySelector('#aura-header-modal')) {
  ReactDOM.render(
    <Header />,
    document.querySelector('#aura-header-modal'),
  );
}
