import React from 'react';
import AuraFormUnlinkedCard from '../../../aura-forms/aura-unlinked-card';
import { getMembersToEarnMessage } from '../../../utilities/checkout_helper';
import getStringMessage from '../../../../../../../js/utilities/strings';

const AuraNotLinkedDataCheckout = (props) => {
  const { cardNumber, pointsToEarn, formActive } = props;
  // Add active or in-active class based on the formActive flag.
  const active = formActive ? 'active' : 'in-active';

  return (
    <div className={`block-content registered-user-unlinked-card ${active}`}>
      <div className="title">
        <div className="subtitle-1">{ getStringMessage('checkout_earn_and_redeem') }</div>
        <div className="subtitle-2">{ getMembersToEarnMessage(pointsToEarn) }</div>
      </div>
      <div className="spc-aura-link-card-form">
        <AuraFormUnlinkedCard cardNumber={cardNumber} formActive={formActive} />
      </div>
    </div>
  );
};

export default AuraNotLinkedDataCheckout;
