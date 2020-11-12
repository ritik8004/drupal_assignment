import React from 'react';
import PointsToEarnMessage from '../../../utilities/points-to-earn';
import PendingEnrollmentMessage from '../../../utilities/pending-enrollment-message';

const AuraLinkedNotVerified = (props) => {
  const { points, loyaltyStatus } = props;

  return (
    <div className="block-content registered-user-linked-pending-enrollment">
      <PointsToEarnMessage points={points} loyaltyStatus={loyaltyStatus} />
      <div className="actions">
        <PendingEnrollmentMessage />
      </div>
    </div>
  );
};

export default AuraLinkedNotVerified;
