import React from 'react';
import Cleave from 'cleave.js/react';
import { getUserProfileInfo } from '../../../../utilities/helper';
import PointsExpiryMessage
  from '../../../../../../alshaya_spc/js/aura-loyalty/components/utilities/points-expiry-message';
import PointsUpgradeMessage
  from '../../../../../../alshaya_spc/js/aura-loyalty/components/utilities/points-upgrade-message';
import ToolTip from '../../../../../../alshaya_spc/js/utilities/tooltip';
import { getTooltipPointsOnHoldMsg } from '../../../../../../alshaya_spc/js/aura-loyalty/components/utilities/helper';

const AuraMyAccountVerifiedUser = (props) => {
  const {
    tierName,
    points,
    cardNumber,
    pointsOnHold,
    upgradeMsg,
    expiringPoints,
    expiryDate,
    firstName,
    lastName,
  } = props;

  const profileInfo = getUserProfileInfo(firstName, lastName);

  return (
    <div className="aura-card-linked-verified-wrapper fadeInUp">
      <div className="aura-card-linked-verified-wrapper-content">
        <div className="aura-logo">
          <div className="aura-user-avatar">{ profileInfo.avatar }</div>
          <div className="aura-user-name">
            { profileInfo.profileName }
            <div className="aura-card-number">
              <span>{Drupal.t('Card number')}</span>
              <span>
                <Cleave
                  name="aura-my-account-user-card"
                  className="aura-my-account-user-card"
                  disabled
                  value={cardNumber}
                  options={{ blocks: [4, 4, 4, 4] }}
                />
              </span>
            </div>
          </div>
        </div>
        <div className="aura-card-linked-verified-description">
          <div className="aura-tier">
            <label>{Drupal.t('Status')}</label>
            <span className="aura-blend">{ tierName }</span>
          </div>
          <div className="aura-points">
            <label>{Drupal.t('Point balance')}</label>
            <span className="aura-blend">{ `${points} ${Drupal.t('pts')}`}</span>
          </div>
          <div className="aura-points-on-hold">
            <label>
              {Drupal.t('Points on hold')}
              <ToolTip enable question>{ getTooltipPointsOnHoldMsg() }</ToolTip>
            </label>
            <span className="aura-blend">{ `${pointsOnHold} ${Drupal.t('pts')}`}</span>
          </div>
        </div>
      </div>
      <PointsUpgradeMessage msg={upgradeMsg} />
      <PointsExpiryMessage points={expiringPoints} date={expiryDate} />
    </div>
  );
};

export default AuraMyAccountVerifiedUser;
