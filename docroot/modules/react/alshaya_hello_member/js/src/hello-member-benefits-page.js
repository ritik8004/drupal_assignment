import React from 'react';
import ReactDOM from 'react-dom';
import isHelloMemberEnabled from '../../../js/helloMemberHelper';
import MyBenefitsPage from './components/my-accounts/my-benefits-page';

if (isHelloMemberEnabled()) {
  if (document.querySelector('#hello-member-benefits-page')) {
    ReactDOM.render(
      <MyBenefitsPage />,
      document.querySelector('#hello-member-benefits-page'),
    );
  }
}
