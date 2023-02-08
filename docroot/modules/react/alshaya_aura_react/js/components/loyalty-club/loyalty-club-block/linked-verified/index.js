import React from 'react';
import { hasValue } from '../../../../../../js/utilities/conditionsUtility';
import AuraProgressWrapper from '../../../aura-progress';
import MyAccountVerifiedUser from './my-account-verified-user';
import MyAuraVerifiedUser from './my-aura-verified-user';

const AuraVerifiedUser = (props) => {
  const {
    tier,
    points,
    pointsOnHold,
    cardNumber,
    firstName,
    lastName,
    upgradeMsg,
    expiringPoints,
    expiryDate,
  } = props;

  if (hasValue(drupalSettings.aura.context)
    && drupalSettings.aura.context === 'my_aura') {
    return (
      <>
        <MyAuraVerifiedUser
          tier={tier}
          points={points}
          pointsOnHold={pointsOnHold}
          cardNumber={cardNumber}
          firstName={firstName}
          lastName={lastName}
        />
        <AuraProgressWrapper
          upgradeMsg={upgradeMsg}
          expiringPoints={expiringPoints}
          expiryDate={expiryDate}
          tier={tier}
        />
      </>
    );
  }

  return (
    <>
      <MyAccountVerifiedUser
        tier={tier}
        points={points}
        pointsOnHold={pointsOnHold}
        cardNumber={cardNumber}
        firstName={firstName}
        lastName={lastName}
        expiringPoints={expiringPoints}
        expiryDate={expiryDate}
      />
    </>
  );
};

export default AuraVerifiedUser;
