import React from 'react';
import PointsToEarnMessage from '../../../utilities/points-to-earn';
import PendingEnrollmentMessage from '../../../utilities/pending-enrollment-message';

const AuraLinkedNotVerified = (props) => {
  const { pointsToEarn, loyaltyStatus, wait } = props;

  return (
    <div className="block-content registered-user-linked-pending-enrollment">
      <PointsToEarnMessage pointsToEarn={pointsToEarn} loyaltyStatus={loyaltyStatus} wait={wait} />
      <div className="actions">
        <PendingEnrollmentMessage />
      </div>
    </div>
  );
};

export default AuraLinkedNotVerified;
