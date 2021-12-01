import React from 'react';
import AuraFormUnlinkedCard from '../../../aura-forms/aura-unlinked-card';
import { getMembersToEarnMessage } from '../../../utilities/checkout_helper';
import getStringMessage from '../../../../../../../js/utilities/strings';

const AuraNotLinkedDataCheckout = (props) => {
  const { cardNumber, price } = props;

  return (
    <div className="block-content registered-user-unlinked-card">
      <div className="title">
        <div className="subtitle-1">{ getStringMessage('checkout_earn_and_redeem') }</div>
        <div className="subtitle-2">{ getMembersToEarnMessage(price) }</div>
      </div>
      <div className="spc-aura-link-card-form">
        <AuraFormUnlinkedCard cardNumber={cardNumber} />
      </div>
    </div>
  );
};

export default AuraNotLinkedDataCheckout;
