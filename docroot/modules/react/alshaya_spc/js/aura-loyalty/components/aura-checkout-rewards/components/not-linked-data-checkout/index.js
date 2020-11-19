import React from 'react';
import AuraFormUnlinkedCard from '../../../aura-forms/aura-unlinked-card';
import PointsString from '../../../utilities/points-string';

const getMembersToEarnMessage = (points) => {
  const toEarnMessageP1 = `${Drupal.t('Members will earn')} `;
  const toEarnMessageP2 = ` ${Drupal.t('with this purchase')}`;

  return (
    <span className="spc-checkout-aura-points-to-earn">
      { toEarnMessageP1 }
      <PointsString points={points} />
      { toEarnMessageP2 }
    </span>
  );
};

const AuraNotLinkedDataCheckout = (props) => {
  const { cardNumber, pointsToEarn } = props;

  return (
    <div className="block-content registered-user-unlinked-card">
      <div className="title">
        <div className="subtitle-1">{ Drupal.t('Earn and redeem as you shop ') }</div>
        <div className="subtitle-2">{ getMembersToEarnMessage(pointsToEarn) }</div>
      </div>
      <div className="spc-aura-link-card-form">
        <AuraFormUnlinkedCard cardNumber={cardNumber} />
      </div>
    </div>
  );
};

export default AuraNotLinkedDataCheckout;
