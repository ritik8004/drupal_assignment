import React from 'react';
import PendingEnrollmentMessage from '../../../utilities/pending-enrollment-message';
import ToolTip from '../../../../../utilities/tooltip';
import PointsString from '../../../utilities/points-string';

const AuraLinkedNotVerifiedCheckout = (props) => {
  const {
    pointsInAccount, pointsToEarn,
  } = props;

  const tooltip = Drupal.t('Your points will be credited to your account but will be on-hold status until the return period of 14 days. After that you will be able to redeem  the points.');

  return (
    <>
      <div className="block-content registered-user-linked-pending-enrollment">
        <div className="title">
          <div className="subtitle-1">
            { Drupal.t('You Have') }
            :
            <PointsString points={pointsInAccount} />
          </div>
          <div className="subtitle-2">
            { Drupal.t('You will earn') }
            :
            <PointsString points={pointsToEarn} />
            { Drupal.t('with this purchase') }
            <ToolTip enable question>{ tooltip }</ToolTip>
          </div>
        </div>
      </div>
      <PendingEnrollmentMessage />
    </>
  );
};

export default AuraLinkedNotVerifiedCheckout;
