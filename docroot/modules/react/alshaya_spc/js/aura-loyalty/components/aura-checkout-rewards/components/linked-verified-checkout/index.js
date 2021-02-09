import React from 'react';
import PointsExpiryMessage from '../../../utilities/points-expiry-message';
import AuraFormRedeemPoints from '../../../aura-forms/aura-redeem-points';
import PointsString from '../../../utilities/points-string';
import ToolTip from '../../../../../utilities/tooltip';
import { getTooltipPointsOnHoldMsg } from '../../../utilities/helper';
import { getPriceToPoint } from '../../../../../../../alshaya_aura_react/js/utilities/aura_utils';
import getStringMessage from '../../../../../../../js/utilities/strings';

const AuraLinkedVerifiedCheckout = (props) => {
  const {
    pointsInAccount,
    price,
    expiringPoints,
    expiryDate,
    cardNumber,
    totals,
  } = props;
  const pointsToEarn = getPriceToPoint(price);

  return (
    <>
      <div className="block-content registered-user-linked">
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
      <AuraFormRedeemPoints
        pointsInAccount={pointsInAccount}
        cardNumber={cardNumber}
        totals={totals}
      />
      <div className="spc-aura-checkout-messages">
        <PointsExpiryMessage points={expiringPoints} date={expiryDate} />
      </div>
    </>
  );
};

export default AuraLinkedVerifiedCheckout;
