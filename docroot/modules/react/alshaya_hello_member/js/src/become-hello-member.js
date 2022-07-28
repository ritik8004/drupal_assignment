import React from 'react';
import ReactDOM from 'react-dom';
import BecomeHelloMember from './components/become-hello-member';

const querySelector = document.querySelector('#hello-member-become-hello-member');
if (querySelector) {
  ReactDOM.render(
    <BecomeHelloMember />,
    querySelector,
  );
}
