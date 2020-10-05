import React from 'react';
import ReactDOM from 'react-dom';
import LoyaltyClubBlock from './components/loyalty-club/loyalty-club-block';

ReactDOM.render(
  <LoyaltyClubBlock
    doNotshowLinkedVerifiedBlock
  />,
  document.querySelector('#my-accounts-aura'),
);
