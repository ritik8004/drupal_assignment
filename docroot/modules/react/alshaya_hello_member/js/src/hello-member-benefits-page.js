import React from 'react';
import ReactDOM from 'react-dom';
import MyBenefitsPage from './components/my-accounts/my-benefits-page';

Drupal.behaviors.alshayaHelloMemberMyBenefitsPageBehavior = {
  attach: function alshayaHelloMemberMyBenefitsPage() {
    jQuery('#hello-member-benefits-page').once('init-react').each(function () {
      ReactDOM.render(
        <MyBenefitsPage />,
        jQuery(this)[0],
      );
    });
  },
};
