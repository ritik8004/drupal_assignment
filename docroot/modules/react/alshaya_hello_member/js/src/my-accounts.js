import React from 'react';
import ReactDOM from 'react-dom';
import MyAccount from './components/my-accounts';

Drupal.behaviors.alshayaHelloMemberMyAccountBehavior = {
  attach: function alshayaHelloMemberMyAccount() {
    jQuery('#my-accounts-hello-member').once('init-react').each(function () {
      ReactDOM.render(
        <MyAccount />,
        jQuery(this)[0],
      );
    });
  },
};
