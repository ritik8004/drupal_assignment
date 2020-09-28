import React from 'react';
import PendingFullEnrollment from './pending-full-enrollment';
import CardNotLinkedMdcData from './card-not-linked-mdc-data';
import CardNotLinkedNoData from './card-not-linked-no-data';
import LinkedVerified from './linked-verified';
import {
  APC_NOT_LINKED_NO_DATA,
  APC_NOT_LINKED_MDC_DATA,
  APC_LINKED_VERIFIED,
  APC_LINKED_NOT_VERIFIED,
}
  from '../../../utilities/constants';
import { getAuraStatus } from '../../../utilities/helper';

const LoyaltyClubBlock = () => {
  const loyaltyStatus = parseInt(getAuraStatus(), 10);

  if (loyaltyStatus !== '') {
    if (loyaltyStatus === APC_NOT_LINKED_NO_DATA) {
      return <CardNotLinkedNoData />;
    } if (loyaltyStatus === APC_NOT_LINKED_MDC_DATA) {
      return <CardNotLinkedMdcData />;
    } if (loyaltyStatus === APC_LINKED_VERIFIED) {
      return <LinkedVerified />;
    } if (loyaltyStatus === APC_LINKED_NOT_VERIFIED) {
      return <PendingFullEnrollment />;
    }
  }

  return (null);
};

export default LoyaltyClubBlock;
