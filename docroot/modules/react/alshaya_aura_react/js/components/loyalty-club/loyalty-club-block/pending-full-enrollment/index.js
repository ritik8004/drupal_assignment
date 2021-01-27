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
          {Drupal.t('Congrats! You are now part of the exclusive AURA club. You’ll now earn points as you shop online and in stores.')}
        </div>
        <div className="description">
          {Drupal.t('To use your points online, please download the AURA app and provide us with a few more details.')}
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
