import React from 'react';
import AppStoreSVG
  from '../../../../../../alshaya_spc/js/svg-component/app-store-svg';
import { getAuraConfig } from '../../../../utilities/helper';

const MyAuraPendingFullEnrollmenttGuest = () => {
  const {
    appStoreLink: appleAppStoreLink,
    googlePlayLink: googlePlayStoreLink,
  } = getAuraConfig();

  return (
    <div className="aura-pending-full-enrollment-wrapper fadeInUp">
      <div className="pending-full-enrollment-description">
        <div className="title">
          {Drupal.t('Congratulations! You are now part of Aura, the loyalty experience personalised for you. You can now earn points when you shop online or instore.', {}, { context: 'aura' })}
        </div>
        <div className="description">
          {Drupal.t('To use your points online, complete your registration on the Aura MENA app.', {}, { context: 'aura' })}
        </div>
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
      </div>
      <div className="aura-model">
        <img loading="lazy" src="/modules/react/alshaya_aura_react/design-assets/model-image@3x.png" />
      </div>
    </div>
  );
};

export default MyAuraPendingFullEnrollmenttGuest;
