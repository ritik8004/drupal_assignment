import React from 'react';
import ReactDOM from 'react-dom';
import AuraPDP from './components/aura-pdp';

if (document.querySelector('#aura-pdp')) {
  ReactDOM.render(
    <AuraPDP />,
    document.querySelector('#aura-pdp'),
  );
}
