import React from 'react';
import _indexOf from 'lodash/indexOf';
import PendingFullEnrollment from './pending-full-enrollment';
import CardNotLinkedMdcData from './card-not-linked-mdc-data';
import CardNotLinkedNoData from './card-not-linked-no-data';
import LinkedVerified from './linked-verified';
import { getAuraStatus, getAllAuraStatus } from '../../../utilities/helper';

const LoyaltyClubBlock = () => {
  const loyaltyStatus = parseInt(getAuraStatus(), 10);
  const allAuraStatus = getAllAuraStatus();

  if (loyaltyStatus !== '') {
    if (loyaltyStatus === _indexOf(allAuraStatus, 'APC_NOT_LINKED_NO_DATA')) {
      return <CardNotLinkedNoData />;
    } if (loyaltyStatus === _indexOf(allAuraStatus, 'APC_NOT_LINKED_MDC_DATA')) {
      return <CardNotLinkedMdcData />;
    } if (loyaltyStatus === _indexOf(allAuraStatus, 'APC_LINKED_VERIFIED')) {
      return <LinkedVerified />;
    } if (loyaltyStatus === _indexOf(allAuraStatus, 'APC_LINKED_NOT_VERIFIED')) {
      return <PendingFullEnrollment />;
    }
  }

  return (null);
};

export default LoyaltyClubBlock;
