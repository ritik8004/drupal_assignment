import React from 'react';
import ReactDOM from 'react-dom';
import isHelloMemberEnabled from '../../../js/utilities/helloMemberHelper';
import MyMembership from './components/my-accounts/my-membership';

if (isHelloMemberEnabled()) {
  if (document.querySelector('#my-membership-info')) {
    ReactDOM.render(
      <MyMembership />,
      document.querySelector('#my-membership-info'),
    );
  }
}
