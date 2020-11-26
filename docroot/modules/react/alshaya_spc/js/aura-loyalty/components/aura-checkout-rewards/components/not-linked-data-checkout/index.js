import React from 'react';
import AuraFormUnlinkedCard from '../../../aura-forms/aura-unlinked-card';
import { getMembersToEarnMessage } from '../../../utilities/checkout_helper';

const AuraNotLinkedDataCheckout = (props) => {
  const { cardNumber, price } = props;

  return (
    <div className="block-content registered-user-unlinked-card">
      <div className="title">
        <div className="subtitle-1">{ Drupal.t('Earn and redeem as you shop ') }</div>
        <div className="subtitle-2">{ getMembersToEarnMessage(price) }</div>
      </div>
      <div className="spc-aura-link-card-form">
        <AuraFormUnlinkedCard cardNumber={cardNumber} />
      </div>
    </div>
  );
};

export default AuraNotLinkedDataCheckout;
