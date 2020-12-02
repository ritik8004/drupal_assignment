import React from 'react';
import AuraMyAccountOldCardFound from './card-not-linked-data';
import AuraMyAccountPendingFullEnrollment from './pending-full-enrollment';
import AuraMyAccountNoLinkedCard from './card-not-linked-no-data';
import AuraMyAccountVerifiedUser from './linked-verified';
import { getAllAuraStatus } from '../../../utilities/helper';
import Loading from '../../../../../alshaya_spc/js/utilities/loading';

const LoyaltyClubBlock = (props) => {
  const allAuraStatus = getAllAuraStatus();
  const {
    wait,
    loyaltyStatus,
    tier,
    points,
    cardNumber,
    expiringPoints,
    expiryDate,
    pointsOnHold,
    upgradeMsg,
    firstName,
    lastName,
  } = props;

  if (wait) {
    return (
      <div className="aura-myaccount-waiting-wrapper">
        <Loading />
      </div>
    );
  }

  const loyaltyStatusInt = parseInt(loyaltyStatus, 10);

  if (loyaltyStatusInt !== '') {
    // When user has no card associated with him.
    if (loyaltyStatusInt === allAuraStatus.APC_NOT_LINKED_NO_DATA
      || loyaltyStatusInt === allAuraStatus.APC_NOT_LINKED_NOT_U) {
      return (
        <AuraMyAccountNoLinkedCard />
      );
    }
    // When user has a old card associated with same email.
    if (loyaltyStatusInt === allAuraStatus.APC_NOT_LINKED_DATA) {
      return (
        <AuraMyAccountOldCardFound
          cardNumber={cardNumber}
        />
      );
    }
    // When user has a verified card.
    if (loyaltyStatusInt === allAuraStatus.APC_LINKED_VERIFIED) {
      return (
        <AuraMyAccountVerifiedUser
          tier={tier}
          points={points}
          expiringPoints={expiringPoints}
          expiryDate={expiryDate}
          pointsOnHold={pointsOnHold}
          upgradeMsg={upgradeMsg}
          cardNumber={cardNumber}
          firstName={firstName}
          lastName={lastName}
        />
      );
    }
    // When user has a card but enrollment is pending.
    if (loyaltyStatusInt === allAuraStatus.APC_LINKED_NOT_VERIFIED) {
      return <AuraMyAccountPendingFullEnrollment />;
    }
  }

  return (null);
};

export default LoyaltyClubBlock;
