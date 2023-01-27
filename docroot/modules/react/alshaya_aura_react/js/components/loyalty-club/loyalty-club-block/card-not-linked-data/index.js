import React from 'react';
import MyAuraBanner from './my-aura-banner';
import MyAccountBanner from './my-account-banner';
import { hasValue } from '../../../../../../js/utilities/conditionsUtility';

const AuraMyAccountOldCardFound = (props) => {
  const { cardNumber, notYouFailed, tier } = props;

  if (hasValue(drupalSettings.aura.context)
    && drupalSettings.aura.context === 'my_aura') {
    return (
      <MyAuraBanner cardNumber={cardNumber} notYouFailed={notYouFailed} tier={tier} />
    );
  }

  return (
    <MyAccountBanner cardNumber={cardNumber} notYouFailed={notYouFailed} tier={tier} />
  );
};

export default AuraMyAccountOldCardFound;
