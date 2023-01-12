import React from 'react';
import MyAuraBanner from './my-aura-banner';
import MyAccountBanner from './my-account-banner';

const AuraMyAccountOldCardFound = (props) => {
  const { cardNumber, notYouFailed } = props;

  if (typeof drupalSettings.aura.context !== 'undefined'
    && drupalSettings.aura.context === 'my_aura') {
    return (
      <MyAuraBanner cardNumber={cardNumber} notYouFailed={notYouFailed} />
    );
  }

  return (
    <MyAccountBanner cardNumber={cardNumber} notYouFailed={notYouFailed} />
  );
};

export default AuraMyAccountOldCardFound;
