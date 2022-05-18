import React from 'react';
import PointsToEarnMessage from '../../../utilities/points-to-earn';

const AuraLinkedNotVerified = (props) => {
  const { pointsToEarn, loyaltyStatus, wait } = props;

  return (
    <div className="block-content registered-user-linked-pending-enrollment">
      <PointsToEarnMessage pointsToEarn={pointsToEarn} loyaltyStatus={loyaltyStatus} wait={wait} />
    </div>
  );
};

export default AuraLinkedNotVerified;
