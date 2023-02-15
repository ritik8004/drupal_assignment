import React from 'react';
import AuraMyAccountOldCardFound from './card-not-linked-data';
import AuraMyAccountPendingFullEnrollment from './pending-full-enrollment';
import AuraMyAccountNoLinkedCard from './card-not-linked-no-data';
import AuraVerifiedUser from './linked-verified';
import { getAllAuraStatus } from '../../../utilities/helper';
import Loading from '../../../../../alshaya_spc/js/utilities/loading';
import { isUserAuthenticated } from '../../../../../js/utilities/helper';
import { getAuraLocalStorageKey } from '../../../utilities/aura_utils';

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
  const localStorageValues = Drupal.getItemFromLocalStorage(getAuraLocalStorageKey());

  if (loyaltyStatusInt !== '') {
    // Guest user and pending enrollment or when user has a card but enrollment
    // is pending.
    if ((localStorageValues !== null && !isUserAuthenticated())
      || loyaltyStatusInt === allAuraStatus.APC_LINKED_NOT_VERIFIED) {
      return (
        <AuraMyAccountPendingFullEnrollment
          cardNumber={cardNumber}
          tier={tier}
          points={points}
          pointsOnHold={pointsOnHold}
          firstName={firstName}
          lastName={lastName}
          loyaltyStatusInt={loyaltyStatusInt}
          upgradeMsg={upgradeMsg}
          expiringPoints={expiringPoints}
          expiryDate={expiryDate}
        />
      );
    }
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
          tier={tier}
        />
      );
    }
    // When user has a verified card.
    if (loyaltyStatusInt === allAuraStatus.APC_LINKED_VERIFIED) {
      return (
        <>
          <AuraVerifiedUser
            tier={tier}
            points={points}
            pointsOnHold={pointsOnHold}
            cardNumber={cardNumber}
            firstName={firstName}
            lastName={lastName}
            upgradeMsg={upgradeMsg}
            expiringPoints={expiringPoints}
            expiryDate={expiryDate}
            loyaltyStatusInt={loyaltyStatusInt}
          />
        </>
      );
    }
  }

  return (null);
};

export default LoyaltyClubBlock;
