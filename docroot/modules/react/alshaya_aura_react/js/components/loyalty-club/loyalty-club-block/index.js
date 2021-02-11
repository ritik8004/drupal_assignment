import React from 'react';
import AuraMyAccountOldCardFound from './card-not-linked-data';
import AuraMyAccountPendingFullEnrollment from './pending-full-enrollment';
import AuraMyAccountNoLinkedCard from './card-not-linked-no-data';
import AuraMyAccountVerifiedUser from './linked-verified';
import { getAllAuraStatus } from '../../../utilities/helper';
import Loading from '../../../../../alshaya_spc/js/utilities/loading';
import AuraProgress from '../../aura-progress';

const LoyaltyClubBlock = (props) => {
  const allAuraStatus = getAllAuraStatus();
  const {
    wait,
    loyaltyStatus,
    tierName,
    points,
    cardNumber,
    expiringPoints,
    expiryDate,
    pointsOnHold,
    upgradeMsg,
    firstName,
    lastName,
    notYouFailed,
    linkCardFailed,
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
          notYouFailed={notYouFailed}
          linkCardFailed={linkCardFailed}
        />
      );
    }
    // When user has a verified card.
    if (loyaltyStatusInt === allAuraStatus.APC_LINKED_VERIFIED) {
      return (
        <>
          <AuraMyAccountVerifiedUser
            tierName={tierName}
            points={points}
            expiringPoints={expiringPoints}
            expiryDate={expiryDate}
            pointsOnHold={pointsOnHold}
            upgradeMsg={upgradeMsg}
            cardNumber={cardNumber}
            firstName={firstName}
            lastName={lastName}
          />
          <AuraProgress />
        </>
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
