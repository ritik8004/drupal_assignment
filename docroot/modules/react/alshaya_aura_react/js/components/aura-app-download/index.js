import React from 'react';
import parse from 'html-react-parser';
import { getAuraConfig } from '../../utilities/helper';
import { isMobile } from '../../../../js/utilities/display';
import { isMyAuraContext } from '../../utilities/aura_utils';
import { hasValue } from '../../../../js/utilities/conditionsUtility';

const AuraAppDownload = () => {
  const {
    appStoreLink: appleAppStoreLink,
    googlePlayLink: googlePlayStoreLink,
  } = getAuraConfig();

  if (isMobile() && isMyAuraContext()) {
    if (hasValue(appleAppStoreLink) && hasValue(googlePlayStoreLink)) {
      return (
        <div className="aura-app-download-wrapper">
          { parse(Drupal.t('To spend your points online, please download Aura Mena app available both on <a href="@appStoreLink">App Store</a> and <a href="@googlePlayLink">Play Store</a>.', { '@appStoreLink': (appleAppStoreLink), '@googlePlayLink': googlePlayStoreLink }, { context: 'aura' })) }
        </div>
      );
    }
  } else {
    return (
      <div className="aura-app-download-wrapper">
        {Drupal.t('To spend your points online, please download Aura Mena app available both on App Store and Play Store.', {}, { context: 'aura' })}
      </div>
    );
  }
  return null;
};

export default AuraAppDownload;
