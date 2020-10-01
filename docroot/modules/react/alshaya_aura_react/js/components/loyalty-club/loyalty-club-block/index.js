import React from 'react';
import AuraMyAccountOldCardFound from './card-not-linked-data';
import AuraMyAccountPendingFullEnrollment from './pending-full-enrollment';
import AuraMyAccountNoLinkedCard from './card-not-linked-no-data';
import AuraMyAccountVerifiedUser from './linked-verified';
import { getUserAuraStatus, getAllAuraStatus } from '../../../utilities/helper';

const LoyaltyClubBlock = () => {
  const loyaltyStatus = parseInt(getUserAuraStatus(), 10);
  const allAuraStatus = getAllAuraStatus();

  if (loyaltyStatus !== '') {
    // When user has no card associated with him.
    if (loyaltyStatus === allAuraStatus.APC_NOT_LINKED_NO_DATA) {
      return <AuraMyAccountNoLinkedCard />;
    }
    // When user has a old card associated with same email.
    if (loyaltyStatus === allAuraStatus.APC_NOT_LINKED_DATA) {
      return <AuraMyAccountOldCardFound />;
    }
    // When user has a verified card.
    if (loyaltyStatus === allAuraStatus.APC_LINKED_VERIFIED) {
      // @TODO: Add condition to not render this on user account page.
      return <AuraMyAccountVerifiedUser />;
    }
    // When user has a card but enrollment is pending.
    if (loyaltyStatus === allAuraStatus.APC_LINKED_NOT_VERIFIED) {
      return <AuraMyAccountPendingFullEnrollment />;
    }
  }

  return (null);
};

export default LoyaltyClubBlock;
