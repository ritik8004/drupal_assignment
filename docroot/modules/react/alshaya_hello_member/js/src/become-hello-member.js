import React from 'react';
import ReactDOM from 'react-dom';
import BecomeHelloMember from '../../../js/utilities/components/become-hello-member';

Drupal.behaviors.alshayaHelloMemberBecomeHelloMember = {
  attach: function alshayaHelloMemberBecomeHelloMember() {
    jQuery('#hello-member-become-hello-member-block').once('init-react').each(function () {
      ReactDOM.render(
        <BecomeHelloMember />,
        jQuery(this)[0],
      );
    });
  },
};
