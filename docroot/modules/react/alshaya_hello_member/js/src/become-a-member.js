import React from 'react';
import ReactDOM from 'react-dom';
import BecomeHelloMember from './components/become-a-member';

const querySelector = document.querySelector('#hello-member-become-a-member');
if (querySelector) {
  ReactDOM.render(
    <BecomeHelloMember />,
    querySelector,
  );
}
