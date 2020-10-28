import React from 'react';
import ReactDOM from 'react-dom';
import Header from './components/header';

if (document.querySelector('#aura-header-modal')) {
  ReactDOM.render(
    <Header />,
    document.querySelector('#aura-header-modal'),
  );
}
