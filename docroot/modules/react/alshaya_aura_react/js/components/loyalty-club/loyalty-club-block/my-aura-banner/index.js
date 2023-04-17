import React from 'react';
import Cleave from 'cleave.js/react';
import {
  getAllAuraStatus, getAllAuraTier, getUserProfileInfo, getAuraConfig,
} from '../../../../utilities/helper';
import { getTooltipPointsOnHoldMsg } from '../../../../utilities/aura_utils';
import ToolTip from '../../../../../../alshaya_spc/js/utilities/tooltip';
import AuraAppDownload from '../../../aura-app-download';
import TrimString from '../../../../../../js/utilities/components/trim-string';
import { hasValue } from '../../../../../../js/utilities/conditionsUtility';

const MyAuraBanner = (props) => {
  const {
    tier,
    points,
    cardNumber,
    pointsOnHold,
    firstName,
    lastName,
    loyaltyStatusInt,
  } = props;

  // Character length of user name to be shown in AURA banner.
  const { auraUsernameCharacterLimit } = getAuraConfig();
  const profileInfo = getUserProfileInfo(firstName, lastName);

  // Current User tier class so we can change gradient for progress bar.
  const currentTierLevel = tier;
  const tierClass = currentTierLevel || 'no-tier';
  const allAuraStatus = getAllAuraStatus();
  const auraUserClass = loyaltyStatusInt === allAuraStatus.APC_LINKED_NOT_VERIFIED ? 'aura-not-verified' : 'aura-verified';

  if (!(hasValue(profileInfo) && hasValue(profileInfo.profileName))) {
    return null;
  }

  return (
    <div className={`aura-card-linked-verified-wrapper fadeInUp aura-level-${tierClass} ${auraUserClass}`}>
      <div className="aura-card-linked-verified-wrapper-content">
        <div className="aura-logo">
          <div className="aura-user-avatar">{profileInfo.avatar}</div>
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
        <div className="aura-card-linked-verified-description">
          <div className="aura-tier">
            <label>{Drupal.t('My tier')}</label>
            <span className="aura-blend">{getAllAuraTier('value')[tier]}</span>
          </div>
          <div className="aura-points">
            <label>{Drupal.t('Points balance')}</label>
            <span className="aura-blend">{`${points} ${Drupal.t('pts')}`}</span>
          </div>
          <div className="aura-points-on-hold">
            <label>
              {Drupal.t('Pending points')}
              <ToolTip enable question>{getTooltipPointsOnHoldMsg()}</ToolTip>
            </label>
            <span className="aura-blend">{`${pointsOnHold} ${Drupal.t('pts')}`}</span>
          </div>
          {loyaltyStatusInt === allAuraStatus.APC_LINKED_NOT_VERIFIED ? (
            <AuraAppDownload />
          ) : ''}
        </div>
      </div>
    </div>
  );
};

export default MyAuraBanner;
