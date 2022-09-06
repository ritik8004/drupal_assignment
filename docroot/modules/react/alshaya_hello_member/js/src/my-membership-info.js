import React from 'react';
import ReactDOM from 'react-dom';
import MyMembership from './components/my-accounts/my-membership';

Drupal.behaviors.alshayaHelloMemberMyMembershipBehavior = {
  attach: function alshayaHelloMemberMyMembership() {
    jQuery('#my-membership-info').once('init-react').each(function fn() {
      ReactDOM.render(
        <MyMembership />,
        jQuery(this)[0],
      );
    });
  },
};
