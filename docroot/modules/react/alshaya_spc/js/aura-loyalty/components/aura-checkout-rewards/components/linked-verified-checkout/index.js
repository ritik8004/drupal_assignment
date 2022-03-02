import React from 'react';
import PointsExpiryMessage from '../../../utilities/points-expiry-message';
import AuraFormRedeemPoints from '../../../aura-forms/aura-redeem-points';
import PointsString from '../../../utilities/points-string';
import ToolTip from '../../../../../utilities/tooltip';
import { getTooltipPointsOnHoldMsg } from '../../../../../../../alshaya_aura_react/js/utilities/aura_utils';
import getStringMessage from '../../../../../../../js/utilities/strings';

const AuraLinkedVerifiedCheckout = (props) => {
  const {
    pointsInAccount,
    pointsToEarn,
    expiringPoints,
    expiryDate,
    cardNumber,
    totals,
    paymentMethodInCart,
    formActive,
  } = props;

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
        paymentMethodInCart={paymentMethodInCart}
        formActive={formActive}
      />
      <div className="spc-aura-checkout-messages">
        <PointsExpiryMessage points={expiringPoints} date={expiryDate} />
      </div>
    </>
  );
};

export default AuraLinkedVerifiedCheckout;
