import React from 'react';
import AppStoreSVG
  from '../../../../../../alshaya_spc/js/svg-component/app-store-svg';
import { getAuraConfig } from '../../../../utilities/helper';

const AuraMyAccountPendingFullEnrollment = () => {
  const {
    appStoreLink: appleAppStoreLink,
    googlePlayLink: googlePlayStoreLink,
  } = getAuraConfig();

  return (
    <div className="aura-pending-full-enrollment-wrapper fadeInUp">
      <div className="pending-full-enrollment-description">
        <div className="title">
          {Drupal.t('Congrats! You are now part of the exclusive Aura Club. Earn points as you shop, whether its online or in-store')}
        </div>
        <div className="description">
          {Drupal.t('To spend your points online, please provide us with a few more details. Download Aura app and complete your profile.')}
        </div>
        <div className="app-links">
          <a href={appleAppStoreLink}><AppStoreSVG store="appstore" /></a>
          <a href={googlePlayStoreLink}><AppStoreSVG store="playstore" /></a>
        </div>
      </div>
      <div className="aura-model">
        <img loading="lazy" src="/modules/react/alshaya_aura_react/design-assets/model-image@3x.png" />
      </div>
    </div>
  );
};

export default AuraMyAccountPendingFullEnrollment;
