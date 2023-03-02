import React from 'react';
import parse from 'html-react-parser';
import { getAuraConfig } from '../../utilities/helper';

const AuraAppDownload = () => {
  const {
    appStoreLink: appleAppStoreLink,
    googlePlayLink: googlePlayStoreLink,
  } = getAuraConfig();

  return (
    <div className="aura-app-download-wrapper">
      { parse(Drupal.t('To spend your points online test, please download Aura Mena app available both on <a href="@appStoreLink">App Store</a> and <a href="@googlePlayLink">Play Store</a>.', { '@appStoreLink': (appleAppStoreLink), '@googlePlayLink': googlePlayStoreLink }, { context: 'aura' })) }
    </div>
  );
};

export default AuraAppDownload;
