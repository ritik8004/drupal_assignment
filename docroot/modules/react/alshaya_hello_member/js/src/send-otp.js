import React from 'react';
import ReactDOM from 'react-dom';
import SendOtpPopup from './components/send-otp-popup';

Drupal.behaviors.alshayaHelloMemberSendOtpPopupBehavior = {
  attach: function alshayaHelloMemberSendOtpPopup() {
    ReactDOM.render(
      <SendOtpPopup />,
      document.getElementById('hello-member-send-otp'),
    );
  },
};
