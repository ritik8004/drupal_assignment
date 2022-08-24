import React from 'react';
import ReactDOM from 'react-dom';
import MyBenefitsPage from './components/my-accounts/my-benefits-page';

Drupal.behaviors.alshayaHelloMemberMyBenefitsPageBehavior = {
  attach: function alshayaHelloMemberMyBenefitsPage() {
    const querySelector = document.querySelector('#hello-member-benefits-page');
    if (querySelector) {
      ReactDOM.render(
        <MyBenefitsPage />,
        querySelector,
      );
    }
  },
};
