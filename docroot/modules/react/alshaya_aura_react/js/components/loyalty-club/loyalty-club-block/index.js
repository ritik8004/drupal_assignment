import React from 'react';
import AuraMyAccountOldCardFound from './card-not-linked-data';
import AuraMyAccountPendingFullEnrollment from './pending-full-enrollment';
import AuraMyAccountNoLinkedCard from './card-not-linked-no-data';
import AuraMyAccountVerifiedUser from './linked-verified';
import { postAPIData } from '../../../utilities/api/fetchApiData';
import { getUserAuraStatus, getAllAuraStatus } from '../../../utilities/helper';

export default class LoyaltyClubBlock extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      loyaltyStatus: getUserAuraStatus(),
    };
  }

  handleNotYou = () => {
    const loyaltyStatus = getAllAuraStatus().APC_NOT_LINKED_NOT_U;
    // API call to update user's loyalty status.
    const apiUrl = 'post/loyalty-club/apc-status-update';
    const data = {
      uid: drupalSettings.aura.user_details.id,
      apcLinkStatus: loyaltyStatus,
    };
    const apiData = postAPIData(apiUrl, data);

    if (apiData instanceof Promise) {
      apiData.then((result) => {
        if (result.data !== undefined && result.data.error === undefined) {
          this.setState({
            loyaltyStatus,
          });
        }
      });
    }
  }

  render() {
    const allAuraStatus = getAllAuraStatus();
    let { loyaltyStatus } = this.state;
    loyaltyStatus = parseInt(loyaltyStatus, 10);

    if (loyaltyStatus !== '') {
      // When user has no card associated with him.
      if (loyaltyStatus === allAuraStatus.APC_NOT_LINKED_NO_DATA
        || loyaltyStatus === allAuraStatus.APC_NOT_LINKED_NOT_U) {
        return <AuraMyAccountNoLinkedCard />;
      }
      // When user has a old card associated with same email.
      if (loyaltyStatus === allAuraStatus.APC_NOT_LINKED_DATA) {
        return (
          <AuraMyAccountOldCardFound
            handleNotYou={this.handleNotYou}
          />
        );
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
  }
}
