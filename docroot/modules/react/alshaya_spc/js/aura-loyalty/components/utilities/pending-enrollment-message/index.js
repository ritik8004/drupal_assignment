import React from 'react';
import AppStoreSVG from '../../../../svg-component/app-store-svg';
import { getAuraConfig } from '../../../../../../alshaya_aura_react/js/utilities/helper';

const PendingEnrollmentMessage = () => {
  const message = Drupal.t('To use your points online, please download the AURA app and provide us with a few more details.');

  const {
    appStoreLink: appleAppStoreLink,
    googlePlayLink: googlePlayStoreLink,
  } = getAuraConfig();

  return (
    <div className="spc-aura-pending-enrollment-message">
      <div className="message">
        {message}
      </div>
      <div className="app-links">
        <a href={appleAppStoreLink}><AppStoreSVG store="appstore" /></a>
        <a href={googlePlayStoreLink}><AppStoreSVG store="playstore" /></a>
      </div>
    </div>
  );
};

export default PendingEnrollmentMessage;
