import React from 'react';
import PointsExpiryMessage from '../../../utilities/points-expiry-message';
import AuraFormRedeemPoints from '../../../aura-forms/aura-redeem-points';
import PointsString from '../../../utilities/points-string';
import ToolTip from '../../../../../utilities/tooltip';

const AuraLinkedVerifiedCheckout = (props) => {
  const {
    pointsInAccount, pointsToEarn, expiringPoints, expiryDate,
  } = props;

  const tooltip = Drupal.t('Your points will be credited to your account but will be on-hold status until the return period of 14 days. After that you will be able to redeem  the points.');

  return (
    <>
      <div className="block-content registered-user-linked">
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
      <AuraFormRedeemPoints />
      <div className="spc-aura-checkout-messages">
        <PointsExpiryMessage points={expiringPoints} date={expiryDate} />
      </div>
    </>
  );
};

export default AuraLinkedVerifiedCheckout;
