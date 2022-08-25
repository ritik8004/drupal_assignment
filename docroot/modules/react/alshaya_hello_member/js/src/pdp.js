import React from 'react';
import ReactDOM from 'react-dom';
import HelloMemberPDP from './components/pdp';

Drupal.behaviors.alshayaHelloMemberPDPBehavior = {
  attach: function alshayaHelloMemberPDP() {
    jQuery('#hello-member-pdp').once('init-react').each(function () {
      ReactDOM.render(
        <HelloMemberPDP />,
        jQuery(this)[0],
      );
    });
  },
};
