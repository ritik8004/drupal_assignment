import React from 'react';
import ReactDOM from 'react-dom';
import MyPointsHistory from './components/my-accounts/my-points-history';

Drupal.behaviors.alshayaHelloMemberMyPointsHistoryBehavior = {
  attach: function alshayaHelloMemberMyPointsHistory() {
    jQuery('#my-accounts-points-history').once('init-react').each(function () {
      ReactDOM.render(
        <MyPointsHistory />,
        jQuery(this)[0],
      );
    });
  },
};
