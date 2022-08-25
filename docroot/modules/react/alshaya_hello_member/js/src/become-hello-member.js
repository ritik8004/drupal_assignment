import React from 'react';
import ReactDOM from 'react-dom';
import BecomeHelloMember from '../../../js/utilities/components/become-hello-member';

Drupal.behaviors.alshayaHelloMemberBecomeHelloMember = {
  attach: function alshayaHelloMemberBecomeHelloMember() {
    const querySelector = document.querySelector('#hello-member-become-hello-member-block');
    if (querySelector) {
      ReactDOM.render(
        <BecomeHelloMember />,
        querySelector,
      );
    }
  },
};
