import React from 'react';
import ReactDOM from 'react-dom';
import HelloMemberPDP from './components/pdp';

const querySelector = document.querySelector('#hello-member-pdp');
if (querySelector) {
  ReactDOM.render(
    <HelloMemberPDP />,
    querySelector,
  );
}
