import React from 'react';
import AppStoreSVG from '../../../../svg-component/app-store-svg';
import { getAuraConfig } from '../../../../../../alshaya_aura_react/js/utilities/helper';

const PendingEnrollmentMessage = () => {
  const message = Drupal.t('To use your points online, please download the Aura MENA app and provide us with a few more details.', {}, { context: 'aura' });

  const {
    appStoreLink: appleAppStoreLink,
    googlePlayLink: googlePlayStoreLink,
  } = getAuraConfig();

  return (
    <div className="spc-aura-pending-enrollment-message-wrapper">
      <div className="spc-aura-pending-enrollment-message">
        <div className="message">
          {message}
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
    </div>
  );
};

export default PendingEnrollmentMessage;
