import React from 'react';
import AppStoreSVG from '../../../../svg-component/app-store-svg';

const PendingEnrollmentMessage = () => {
  const message = Drupal.t('To spend your points online, please provide us with a few more details. Download Aura app and complete your profile.');

  // App store links.
  const googlePlayStoreLink = '#';
  const appleAppStoreLink = '#';

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
