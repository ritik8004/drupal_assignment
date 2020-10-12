import React from 'react';
import AuraMyAccountOldCardFound from './card-not-linked-data';
import AuraMyAccountPendingFullEnrollment from './pending-full-enrollment';
import AuraMyAccountNoLinkedCard from './card-not-linked-no-data';
import AuraMyAccountVerifiedUser from './linked-verified';
import { postAPIData } from '../../../utilities/api/fetchApiData';
import { getAllAuraStatus } from '../../../utilities/helper';

export default class LoyaltyClubBlock extends React.Component {
  handleNotYou = (cardNumber) => {
    const loyaltyStatusNotU = getAllAuraStatus().APC_NOT_LINKED_NO_DATA;
    this.updateUsersLoyaltyStatus(cardNumber, loyaltyStatusNotU, 'N', getAllAuraStatus().APC_NOT_LINKED_NOT_U);
  }

  handleLinkYourCardClick = (cardNumber) => {
    const loyaltyStatus = getAllAuraStatus().APC_NOT_LINKED_DATA;
    this.updateUsersLoyaltyStatus(cardNumber, loyaltyStatus, 'Y', getAllAuraStatus().APC_LINKED_NOT_VERIFIED);
  }

  updateUsersLoyaltyStatus = (cardNumber, auraStatus, link, updatedAuraStatus) => {
    // API call to update user's loyalty status.
    const apiUrl = 'post/loyalty-club/apc-status-update';
    const data = {
      uid: drupalSettings.aura.user_details.id,
      apcIdentifierId: cardNumber,
      apcLinkStatus: auraStatus,
      link,
      updatedAuraStatus,
    };
    const apiData = postAPIData(apiUrl, data);

    if (apiData instanceof Promise) {
      apiData.then((result) => {
        if (result.data !== undefined && result.data.error === undefined) {
          if (result.data.status) {
            const { updateLoyaltyStatus } = this.props;
            updateLoyaltyStatus(updatedAuraStatus);
          }
        }
      });
    }
  }

  render() {
    const allAuraStatus = getAllAuraStatus();
    const { loyaltyStatus } = this.props;
    const loyaltyStatusInt = parseInt(loyaltyStatus, 10);

    if (loyaltyStatusInt !== '') {
      // When user has no card associated with him.
      if (loyaltyStatusInt === allAuraStatus.APC_NOT_LINKED_NO_DATA
        || loyaltyStatusInt === allAuraStatus.APC_NOT_LINKED_NOT_U) {
        return <AuraMyAccountNoLinkedCard />;
      }
      // When user has a old card associated with same email.
      if (loyaltyStatusInt === allAuraStatus.APC_NOT_LINKED_DATA) {
        return (
          <AuraMyAccountOldCardFound
            handleNotYou={this.handleNotYou}
            handleLinkYourCardClick={this.handleLinkYourCardClick}
          />
        );
      }
      // When user has a verified card.
      if (loyaltyStatusInt === allAuraStatus.APC_LINKED_VERIFIED) {
        return <AuraMyAccountVerifiedUser />;
      }
      // When user has a card but enrollment is pending.
      if (loyaltyStatusInt === allAuraStatus.APC_LINKED_NOT_VERIFIED) {
        return <AuraMyAccountPendingFullEnrollment />;
      }
    }

    return (null);
  }
}
