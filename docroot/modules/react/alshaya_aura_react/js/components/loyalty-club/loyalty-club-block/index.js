import React from 'react';
import AuraMyAccountOldCardFound from './card-not-linked-data';
import AuraMyAccountPendingFullEnrollment from './pending-full-enrollment';
import AuraMyAccountNoLinkedCard from './card-not-linked-no-data';
import AuraMyAccountVerifiedUser from './linked-verified';
import { postAPIData } from '../../../utilities/api/fetchApiData';
import { getAllAuraStatus, getUserDetails } from '../../../utilities/helper';

export default class LoyaltyClubBlock extends React.Component {
  handleNotYou = (cardNumber) => {
    this.updateUsersLoyaltyStatus(cardNumber, getAllAuraStatus().APC_NOT_LINKED_NOT_U, 'N');
  }

  handleLinkYourCardClick = (cardNumber) => {
    this.updateUsersLoyaltyStatus(cardNumber, getAllAuraStatus().APC_LINKED_NOT_VERIFIED, 'Y');
  }

  updateUsersLoyaltyStatus = (cardNumber, auraStatus, link) => {
    // API call to update user's loyalty status.
    const apiUrl = 'post/loyalty-club/apc-status-update';
    const data = {
      uid: getUserDetails().id,
      apcIdentifierId: cardNumber,
      apcLinkStatus: auraStatus,
      link,
    };
    const apiData = postAPIData(apiUrl, data);

    if (apiData instanceof Promise) {
      apiData.then((result) => {
        if (result.data !== undefined && result.data.error === undefined) {
          if (result.data.status) {
            const { updateLoyaltyStatus } = this.props;
            updateLoyaltyStatus(auraStatus);
          }
        }
      });
    }
  }

  render() {
    const allAuraStatus = getAllAuraStatus();
    const {
      loyaltyStatus, tier, points, cardNumber, expiringPoints, expiryDate, pointsOnHold, upgradeMsg,
    } = this.props;
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
            cardNumber={cardNumber}
          />
        );
      }
      // When user has a verified card.
      if (loyaltyStatusInt === allAuraStatus.APC_LINKED_VERIFIED) {
        return (
          <AuraMyAccountVerifiedUser
            tier={tier}
            points={points}
            expiringPoints={expiringPoints}
            expiryDate={expiryDate}
            pointsOnHold={pointsOnHold}
            upgradeMsg={upgradeMsg}
          />
        );
      }
      // When user has a card but enrollment is pending.
      if (loyaltyStatusInt === allAuraStatus.APC_LINKED_NOT_VERIFIED) {
        return <AuraMyAccountPendingFullEnrollment />;
      }
    }

    return (null);
  }
}
