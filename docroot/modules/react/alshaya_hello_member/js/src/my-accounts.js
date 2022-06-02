import React from 'react';
import ReactDOM from 'react-dom';
import isHelloMemberEnabled from '../../../js/utilities/helloMemberHelper';
import MyAccount from './components/my-accounts';

if (isHelloMemberEnabled()) {
  if (document.querySelector('#my-accounts-hello-member')) {
    ReactDOM.render(
      <MyAccount />,
      document.querySelector('#my-accounts-hello-member'),
    );
  }
}
