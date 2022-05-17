import React from 'react';
import AppStoreSVG from '../../../../svg-component/app-store-svg';
import { getAuraConfig } from '../../../../../../alshaya_aura_react/js/utilities/helper';

const AuraAppLinks = () => {
  const {
    appStoreLink: appleAppStoreLink,
    googlePlayLink: googlePlayStoreLink,
  } = getAuraConfig();

  return (
    <div className="app-links">
      <a
        href={appleAppStoreLink}
        target="_blank"
        rel="noopener noreferrer"
      >
        <AppStoreSVG store="appstore" />
      </a>
      <a
        href={googlePlayStoreLink}
        target="_blank"
        rel="noopener noreferrer"
      >
        <AppStoreSVG store="playstore" />
      </a>
    </div>
  );
};

export default AuraAppLinks;
