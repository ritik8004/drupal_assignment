import React from 'react';
import ReactDOM from 'react-dom';
import isHelloMemberEnabled from '../../../js/helloMemberHelper';
import MyPointsHistory from './components/my-accounts/my-points-history';


if (isHelloMemberEnabled()) {
  if (document.querySelector('#my-accounts-points-history')) {
    ReactDOM.render(
      <MyPointsHistory />,
      document.querySelector('#my-accounts-points-history'),
    );
  }
}
