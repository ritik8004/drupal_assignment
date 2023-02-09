import React from 'react';
import { getAuraContext } from '../../../../utilities/aura_utils';
import AuraProgressWrapper from '../../../aura-progress';
import MyAuraBanner from '../my-aura-banner';
import MyAccountVerifiedUser from './my-account-verified-user';

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
    loyaltyStatusInt,
  } = props;

  if (getAuraContext() === 'my_aura') {
    return (
      <>
        <MyAuraBanner
          tier={tier}
          points={points}
          pointsOnHold={pointsOnHold}
          cardNumber={cardNumber}
          firstName={firstName}
          lastName={lastName}
          loyaltyStatusInt={loyaltyStatusInt}
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
