import React from 'react';
import AuraMyAccountOldCardFound from './card-not-linked-data';
import AuraMyAccountPendingFullEnrollment from './pending-full-enrollment';
import AuraMyAccountNoLinkedCard from './card-not-linked-no-data';
import AuraMyAccountVerifiedUser from './linked-verified';
import { postAPIData } from '../../../utilities/api/fetchApiData';
import { getAllAuraStatus, getUserDetails } from '../../../utilities/helper';
import {
  showFullScreenLoader,
  removeFullScreenLoader,
} from '../../../../../js/utilities/showRemoveFullScreenLoader';
import Loading from '../../../../../alshaya_spc/js/utilities/loading';
import dispatchCustomEvent from '../../../../../js/utilities/events';

export default class LoyaltyClubBlock extends React.Component {
  componentDidMount() {
    document.addEventListener('customerClickedNotYouHeader', this.updateLoyaltyStatus, false);
    document.addEventListener('customerSignedUpHeader', this.updateLoyaltyStatus, false);
  }

  updateLoyaltyStatus = (auraStatus) => {
    const { updateLoyaltyStatus } = this.props;
    updateLoyaltyStatus(auraStatus.detail);
  }

  handleNotYou = (cardNumber) => {
    const auraStatus = getAllAuraStatus().APC_NOT_LINKED_NOT_U;

    this.updateUsersLoyaltyStatus(cardNumber, auraStatus, 'N');
    dispatchCustomEvent('customerClickedNotYouLoyaltyBlock', auraStatus);
  }

  handleLinkYourCardClick = (cardNumber) => {
    const auraStatus = getAllAuraStatus().APC_LINKED_NOT_VERIFIED;

    this.updateUsersLoyaltyStatus(cardNumber, auraStatus, 'Y');
    dispatchCustomEvent('customerClickedLinkCardLoyaltyBlock', auraStatus);
  }

  handleSignUp = () => {
    const { updateLoyaltyStatus } = this.props;
    const auraStatus = getAllAuraStatus().APC_LINKED_NOT_VERIFIED;

    updateLoyaltyStatus(auraStatus);
    dispatchCustomEvent('customerSignedUpLoyaltyBlock', auraStatus);
  }

  updateUsersLoyaltyStatus = (cardNumber, auraStatus, link) => {
    // API call to update user's loyalty status.
    showFullScreenLoader();
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
        removeFullScreenLoader();
      });
    }
  }

  render() {
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
    } = this.props;

    if (wait) {
      return (
        <div className="aura-myaccount-waiting-wrapper">
          <Loading />
        </div>
      );
    }

    const loyaltyStatusInt = parseInt(loyaltyStatus, 10);

    if (loyaltyStatusInt !== '') {
      // When user has no card associated with him.
      if (loyaltyStatusInt === allAuraStatus.APC_NOT_LINKED_NO_DATA
        || loyaltyStatusInt === allAuraStatus.APC_NOT_LINKED_NOT_U) {
        return (
          <AuraMyAccountNoLinkedCard
            handleSignUp={this.handleSignUp}
          />
        );
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
            cardNumber={cardNumber}
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
