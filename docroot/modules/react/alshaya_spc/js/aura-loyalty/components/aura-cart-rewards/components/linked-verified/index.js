import React from 'react';
import PointsToEarnMessage from '../../../utilities/points-to-earn';
import PointsExpiryMessage from '../../../utilities/points-expiry-message';

const AuraLinkedVerified = (props) => {
  const {
    price, expiringPoints, expiryDate, loyaltyStatus,
  } = props;

  return (
    <div className="block-content registered-user-linked">
      <PointsToEarnMessage price={price} loyaltyStatus={loyaltyStatus} />
      <div className="actions">
        <PointsExpiryMessage points={expiringPoints} date={expiryDate} />
      </div>
    </div>
  );
};

export default AuraLinkedVerified;
