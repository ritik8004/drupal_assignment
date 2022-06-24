import React from 'react';
import ReactDOM from 'react-dom';
import isHelloMemberEnabled from '../../../js/utilities/helloMemberHelper';
import HelloMemberPDP from './components/pdp';

if (isHelloMemberEnabled() && document.querySelector('#hello-member-pdp')) {
  ReactDOM.render(
    <HelloMemberPDP mode="main" />,
    document.querySelector('#hello-member-pdp'),
  );
}
