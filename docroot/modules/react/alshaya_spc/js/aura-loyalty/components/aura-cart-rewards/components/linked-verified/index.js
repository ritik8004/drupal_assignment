import React from 'react';
import PointsToEarnMessage from '../../../utilities/points-to-earn';
import PointsExpiryMessage from '../../../utilities/points-expiry-message';

const AuraLinkedVerified = (props) => {
  const {
    pointsToEarn, expiringPoints, expiryDate, loyaltyStatus, wait,
  } = props;

  return (
    <>
      <div className="block-content registered-user-linked">
        <PointsToEarnMessage
          pointsToEarn={pointsToEarn}
          loyaltyStatus={loyaltyStatus}
          wait={wait}
        />
      </div>
      <div className="actions">
        <PointsExpiryMessage
          points={expiringPoints}
          date={expiryDate}
        />
      </div>
    </>
  );
};

export default AuraLinkedVerified;
