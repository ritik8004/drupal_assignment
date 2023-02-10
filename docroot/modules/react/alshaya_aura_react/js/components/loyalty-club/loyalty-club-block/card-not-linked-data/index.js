import React from 'react';
import MyAuraBanner from './my-aura-banner';
import MyAccountBanner from './my-account-banner';
import { isMyAuraContext } from '../../../../utilities/aura_utils';

const AuraMyAccountOldCardFound = (props) => {
  const { cardNumber, notYouFailed, tier } = props;

  if (isMyAuraContext()) {
    return (
      <MyAuraBanner cardNumber={cardNumber} notYouFailed={notYouFailed} tier={tier} />
    );
  }

  return (
    <MyAccountBanner cardNumber={cardNumber} notYouFailed={notYouFailed} tier={tier} />
  );
};

export default AuraMyAccountOldCardFound;
