import Cleave from 'cleave.js/react';
import React from 'react';
import AppStoreSVG
  from '../../../../../../alshaya_spc/js/svg-component/app-store-svg';
import { isMobile } from '../../../../../../js/utilities/display';
import AuraLogo from '../../../../svg-component/aura-logo';
import { getAuraContext } from '../../../../utilities/aura_utils';
import { getAuraConfig } from '../../../../utilities/helper';
import MyAuraBanner from '../my-aura-banner';

const AuraMyAccountPendingFullEnrollment = (props) => {
  const {
    appStoreLink: appleAppStoreLink,
    googlePlayLink: googlePlayStoreLink,
  } = getAuraConfig();

  const {
    cardNumber,
    tier,
    points,
    pointsOnHold,
    firstName,
    lastName,
    loyaltyStatusInt,
  } = props;

  if (getAuraContext() === 'my_aura') {
    return (
      <>
        <MyAuraBanner
          tier={tier}
          points={points}
          pointsOnHold={pointsOnHold}
          cardNumber={cardNumber}
          firstName={firstName}
          lastName={lastName}
          loyaltyStatusInt={loyaltyStatusInt}
        />
      </>
    );
  }

  return (
    <div className="aura-pending-full-enrollment-wrapper fadeInUp">
      <div className="aura-logo">
        <AuraLogo />
      </div>
      <div className="card-number-wrapper">
        <div className="card-number-label">
          {Drupal.t('Aura membership number', {}, { context: 'aura' })}
        </div>
        <Cleave
          name="aura-my-account-link-card"
          className="aura-my-account-link-card"
          disabled
          value={cardNumber}
          options={{ blocks: [4, 4, 4, 4] }}
        />
      </div>
      <div className="pending-full-enrollment-description">
        <div className="description">
          {Drupal.t('To spend your points online, please download Aura Mena app available both on App Store and Play Store.', {}, { context: 'aura' })}
        </div>

        {isMobile() && (
          <div className="app-links">
            <a
              href={appleAppStoreLink}
              target="_blank"
              rel="noopener noreferrer"
              onClick={() => Drupal.alshayaSeoGtmPushAuraEventData({ action: 'AURA_EVENT_ACTION_CLICK_APPSTORE' })}
            >
              <AppStoreSVG store="appstore" />
            </a>
            <a
              href={googlePlayStoreLink}
              target="_blank"
              rel="noopener noreferrer"
              onClick={() => Drupal.alshayaSeoGtmPushAuraEventData({ action: 'AURA_EVENT_ACTION_CLICK_PLAYSTORE' })}
            >
              <AppStoreSVG store="playstore" />
            </a>
          </div>
        )}
      </div>
      <div className="aura-model">
        <img loading="lazy" src="/modules/react/alshaya_aura_react/design-assets/model-image@3x.png" />
      </div>
    </div>
  );
};

export default AuraMyAccountPendingFullEnrollment;
