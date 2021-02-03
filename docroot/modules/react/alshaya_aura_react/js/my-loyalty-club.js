import React from 'react';
import ReactDOM from 'react-dom';
import LoyaltyClub from './components/loyalty-club';
import isAuraEnabled from '../../js/utilities/helper';

if (isAuraEnabled()) {
  ReactDOM.render(
    <LoyaltyClub />,
    document.querySelector('#my-loyalty-club'),
  );
}
