import React from 'react';
import ReactDOM from 'react-dom';
import SendOtpPopup from './components/send-otp-popup';

Drupal.behaviors.alshayaHelloMemberSendOtpPopupBehavior = {
  attach: function alshayaHelloMemberSendOtpPopup() {
    jQuery('#hello-member-send-otp').once('init-react').each(function fn() {
      ReactDOM.render(
        <SendOtpPopup />,
        jQuery(this)[0],
      );
    });
  },
};
