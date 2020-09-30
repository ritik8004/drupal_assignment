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
    if (loyaltyStatus === allAuraStatus.APC_NOT_LINKED_NO_DATA) {
      return <AuraMyAccountNoLinkedCard />;
    } if (loyaltyStatus === allAuraStatus.APC_NOT_LINKED_DATA) {
      return <AuraMyAccountOldCardFound />;
    } if (loyaltyStatus === allAuraStatus.APC_LINKED_VERIFIED) {
      // @TODO: Add condition to not render this on user account page.
      return <AuraMyAccountVerifiedUser />;
    } if (loyaltyStatus === allAuraStatus.APC_LINKED_NOT_VERIFIED) {
      return <AuraMyAccountPendingFullEnrollment />;
    }
  }

  return (null);
};

export default LoyaltyClubBlock;
