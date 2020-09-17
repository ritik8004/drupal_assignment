import React from 'react';
import PendingFullEnrollment from './pending-full-enrollment';
import CardNotLinkedMdcData from './card-not-linked-mdc-data';
import CardNotLinkedNoData from './card-not-linked-no-data';

const LoyaltyClubBlock = () => {
  if (typeof drupalSettings.alshaya_aura !== 'undefined'
    && typeof drupalSettings.alshaya_aura.user_details !== 'undefined'
    && ({}).hasOwnProperty.call(drupalSettings.alshaya_aura.user_details, 'loyaltyStatus')) {
    let loyaltyStatus = drupalSettings.alshaya_aura.user_details.loyaltyStatus;

    if (loyaltyStatus == 0) {
      return <CardNotLinkedNoData/>;
    } else if (loyaltyStatus == 1) {
      return <CardNotLinkedMdcData/>;
    } else if (loyaltyStatus == 3) {
      return <PendingFullEnrollment/>;
    }
  }

  return (null);
};

export default LoyaltyClubBlock;
