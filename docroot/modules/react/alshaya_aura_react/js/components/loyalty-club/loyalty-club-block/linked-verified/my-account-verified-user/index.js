import React from 'react';
import Cleave from 'cleave.js/react';
import { getAllAuraTier, getUserProfileInfo, getAuraConfig } from '../../../../../utilities/helper';
import { getTooltipPointsOnHoldMsg } from '../../../../../utilities/aura_utils';
import PointsExpiryMessage from '../../../../../../../alshaya_spc/js/aura-loyalty/components/utilities/points-expiry-message';
import ToolTip from '../../../../../../../alshaya_spc/js/utilities/tooltip';
import { isDesktop, isMobile } from '../../../../../../../js/utilities/display';
import TrimString from '../../../../../../../js/utilities/components/trim-string';
import { hasValue } from '../../../../../../../js/utilities/conditionsUtility';

const MyAccountVerifiedUser = (props) => {
  const {
    tier,
    points,
    cardNumber,
    pointsOnHold,
    firstName,
    lastName,
    expiringPoints,
    expiryDate,
  } = props;

  // Character length of user name to be shown in AURA banner.
  const { auraUsernameCharacterLimit } = getAuraConfig();
  const profileInfo = getUserProfileInfo(firstName, lastName);

  // Current User tier class so we can change gradient for progress bar.
  const currentTierLevel = tier;
  const tierClass = currentTierLevel || 'no-tier';

  if (!(hasValue(profileInfo) && hasValue(profileInfo.profileName))) {
    return null;
  }

  return (
    <div className="aura-my-account-verified-wrapper">
      <div className={`aura-card-linked-verified-wrapper fadeInUp aura-level-${tierClass}`}>
        <div className="aura-card-linked-verified-wrapper-content">
          <div className="aura-logo">
            <div className="aura-user-name">
              <div title={profileInfo.profileName}>
                <TrimString
                  stringToTrim={profileInfo.profileName}
                  desktopCharacterLimit={auraUsernameCharacterLimit}
                  showEllipsis
                />
              </div>
              <div className="aura-card-number">
                <span>{Drupal.t('Aura membership number', {}, { context: 'aura' })}</span>
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
          {isDesktop()
            && (
            <div className="aura-card-linked-verified-description">
              <div className="aura-tier">
                <label>{Drupal.t('My tier')}</label>
                <span className="aura-blend">{ getAllAuraTier('value')[tier] }</span>
              </div>
              <div className="aura-points">
                <label>{Drupal.t('Points balance')}</label>
                <span className="aura-blend">{ `${points} ${Drupal.t('pts')}`}</span>
              </div>
              <div className="aura-points-on-hold">
                <label>
                  {Drupal.t('Pending points')}
                  <ToolTip enable question>{ getTooltipPointsOnHoldMsg() }</ToolTip>
                </label>
                <span className="aura-blend">{ `${pointsOnHold} ${Drupal.t('pts')}`}</span>
              </div>
            </div>
            )}
          {isMobile()
            && (
            <div className="aura-card-linked-verified-mobile">
              <span className="aura-blend">{ `${pointsOnHold} ${Drupal.t('pts')}`}</span>
            </div>
            )}
        </div>
      </div>
      <PointsExpiryMessage
        points={expiringPoints}
        date={expiryDate}
      />
    </div>
  );
};

export default MyAccountVerifiedUser;
