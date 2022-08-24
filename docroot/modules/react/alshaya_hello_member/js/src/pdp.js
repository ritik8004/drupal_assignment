import React from 'react';
import ReactDOM from 'react-dom';
import HelloMemberPDP from './components/pdp';

Drupal.behaviors.alshayaHelloMemberPDPBehavior = {
  attach: function alshayaHelloMemberPDP() {
    const querySelector = document.querySelector('#hello-member-pdp');
    if (querySelector) {
      ReactDOM.render(
        <HelloMemberPDP />,
        querySelector,
      );
    }
  },
};
