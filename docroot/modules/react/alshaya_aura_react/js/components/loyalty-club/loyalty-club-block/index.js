import React from 'react';
import CardNotLinkedData from './card-not-linked-data';
import AuraMyAccountPendingFullEnrollment from './pending-full-enrollment';
import CardNotLinkedNoData from './card-not-linked-no-data';
import { getAPIData } from '../../../utilities/api/fetchApiData';
import LinkedVerified from './linked-verified';
import { getUserAuraStatus, getAllAuraStatus } from '../../../utilities/helper';

export default class LoyaltyClubBlock extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      loyaltyStatus: getUserAuraStatus(),
    };
  }

  handleNotYou = () => {
    // @TODO: Discuss/Update the status value when user clicked on Not You?.
    const loyaltyStatus = 0;
    // API call to update user's loyalty status.
    const apiUrl = `get/loyalty-club/apc-status-update?uid=${drupalSettings.aura.user_details.id}&apcLinkStatus=${loyaltyStatus}`;
    const apiData = getAPIData(apiUrl);

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
      if (loyaltyStatus === allAuraStatus.APC_NOT_LINKED_NO_DATA) {
        return <CardNotLinkedNoData />;
      } if (loyaltyStatus === allAuraStatus.APC_NOT_LINKED_DATA) {
        return (
          <CardNotLinkedData
            handleNotYou={this.handleNotYou}
          />
        );
      } if (loyaltyStatus === allAuraStatus.APC_LINKED_VERIFIED) {
        // @TODO: Add condition to not render this on user account page.
        return <LinkedVerified />;
      } if (loyaltyStatus === allAuraStatus.APC_LINKED_NOT_VERIFIED) {
        return <AuraMyAccountPendingFullEnrollment />;
      }
    }

    return (null);
  }
}
