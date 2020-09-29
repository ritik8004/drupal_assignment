import React from 'react';
import PendingFullEnrollment from './pending-full-enrollment';
import CardNotLinkedData from './card-not-linked-data';
import CardNotLinkedNoData from './card-not-linked-no-data';
import LinkedVerified from './linked-verified';
import { getUserAuraStatus, getAllAuraStatus } from '../../../utilities/helper';

const LoyaltyClubBlock = () => {
  const loyaltyStatus = parseInt(getUserAuraStatus(), 10);
  const allAuraStatus = getAllAuraStatus();

  if (loyaltyStatus !== '') {
    if (loyaltyStatus === allAuraStatus.APC_NOT_LINKED_NO_DATA) {
      return <CardNotLinkedNoData />;
    } if (loyaltyStatus === allAuraStatus.APC_NOT_LINKED_DATA) {
      return <CardNotLinkedData />;
    } if (loyaltyStatus === allAuraStatus.APC_LINKED_VERIFIED) {
      // @TODO: Add condition to not render this on user account page.
      return <LinkedVerified />;
    } if (loyaltyStatus === allAuraStatus.APC_LINKED_NOT_VERIFIED) {
      return <PendingFullEnrollment />;
    }
  }

  return (null);
};

export default LoyaltyClubBlock;
