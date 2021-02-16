import React from 'react';
import AppStoreSVG
  from '../../../../../alshaya_spc/js/svg-component/app-store-svg';
import { getAuraConfig } from '../../../utilities/helper';

const EmptyRewardActivity = () => {
  const {
    appStoreLink: appleAppStoreLink,
    googlePlayLink: googlePlayStoreLink,
    siteName,
  } = getAuraConfig();

  return (
    <>
      <div className="empty-reward-activity-content">
        <span>{`${Drupal.t('You have no previous Aura transactions with')} `}</span>
        <span className="highlight">{siteName}</span>
        <span>{` ${Drupal.t('to display.')}`}</span>
      </div>
      <div>
        {`${Drupal.t('To view rewards activity across all our brands, visit our Loyalty app')} `}
        <div className="app-links">
          <a href={appleAppStoreLink}><AppStoreSVG store="appstore" /></a>
          <a href={googlePlayStoreLink}><AppStoreSVG store="playstore" /></a>
        </div>
      </div>
    </>
  );
};

export default EmptyRewardActivity;
