import React from 'react';
import PointsToEarnMessage from '../../../utilities/points-to-earn';
import AuraFormUnlinkedCard from '../../../aura-forms/aura-unlinked-card';

const AuraNotLinkedData = (props) => {
  const {
    pointsToEarn, cardNumber, loyaltyStatus, wait,
  } = props;

  return (
    <div className="block-content registered-user-unlinked-card">
      <PointsToEarnMessage pointsToEarn={pointsToEarn} loyaltyStatus={loyaltyStatus} wait={wait} />
      <div className="actions">
        <AuraFormUnlinkedCard cardNumber={cardNumber} />
      </div>
    </div>
  );
};

export default AuraNotLinkedData;
