import React from 'react';
import ReactDOM from 'react-dom';
import BecomeMember from './components/become-a-member';

const querySelector = document.querySelector('#hello-member-become-a-member');
if (querySelector) {
  ReactDOM.render(
    <BecomeMember />,
    querySelector,
  );
}
