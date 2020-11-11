import React from 'react';
import PointsToEarnMessage from '../../../utilities/points-to-earn';
import AuraFormUnlinkedCard from '../../../aura-forms/aura-unlinked-card';

const AuraNotLinkedData = (props) => {
  const { points, cardNumber, loyaltyStatus } = props;

  return (
    <div className="block-content registered-user-unlinked-card">
      <PointsToEarnMessage points={points} loyaltyStatus={loyaltyStatus} />
      <div className="actions">
        <AuraFormUnlinkedCard cardNumber={cardNumber} />
      </div>
    </div>
  );
};

export default AuraNotLinkedData;
