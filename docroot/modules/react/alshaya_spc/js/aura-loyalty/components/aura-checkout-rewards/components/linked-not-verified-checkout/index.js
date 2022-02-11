import React from 'react';
import PendingEnrollmentMessage from '../../../utilities/pending-enrollment-message';
import ToolTip from '../../../../../utilities/tooltip';
import PointsString from '../../../utilities/points-string';
import { getTooltipPointsOnHoldMsg } from '../../../../../../../alshaya_aura_react/js/utilities/aura_utils';
import getStringMessage from '../../../../../../../js/utilities/strings';

const AuraLinkedNotVerifiedCheckout = (props) => {
  const {
    pointsInAccount, pointsToEarn,
  } = props;

  return (
    <>
      <div className="block-content registered-user-linked-pending-enrollment">
        <div className="title">
          <div className="subtitle-1">
            { getStringMessage('checkout_you_have') }
            :
            <PointsString points={pointsInAccount} />
          </div>
          <div className="subtitle-2">
            { getStringMessage('checkout_you_will_earn') }
            :
            <PointsString points={pointsToEarn} />
            { getStringMessage('checkout_with_this_purchase') }
            <ToolTip enable question>{ getTooltipPointsOnHoldMsg() }</ToolTip>
          </div>
        </div>
      </div>
      <PendingEnrollmentMessage />
    </>
  );
};

export default AuraLinkedNotVerifiedCheckout;
