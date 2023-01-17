import React from 'react';
import AuraMyAccountOldCardFound from './card-not-linked-data';
import AuraMyAccountPendingFullEnrollment from './pending-full-enrollment';
import AuraMyAccountNoLinkedCard from './card-not-linked-no-data';
import AuraMyAccountVerifiedUser from './linked-verified';
import { getAllAuraStatus } from '../../../utilities/helper';
import Loading from '../../../../../alshaya_spc/js/utilities/loading';
import AuraProgressWrapper from '../../aura-progress';
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
    // Guest user and pending enrollment.
    if (localStorageValues !== null && !isUserAuthenticated()) {
      return (
        <AuraMyAccountPendingFullEnrollment />
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
          <AuraMyAccountVerifiedUser
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
    // When user has a card but enrollment is pending.
    if (loyaltyStatusInt === allAuraStatus.APC_LINKED_NOT_VERIFIED) {
      return <AuraMyAccountPendingFullEnrollment />;
    }
  }

  return (null);
};

export default LoyaltyClubBlock;
