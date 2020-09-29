import React from 'react';
import PendingFullEnrollment from './pending-full-enrollment';
import CardNotLinkedMdcData from './card-not-linked-mdc-data';
import CardNotLinkedNoData from './card-not-linked-no-data';
import { getAPIData } from '../../../utilities/api/fetchApiData';

export default class LoyaltyClubBlock extends React.Component {
  constructor(props) {
    super(props);
    if (typeof drupalSettings.aura !== 'undefined'
      && typeof drupalSettings.aura.user_details !== 'undefined'
      && ({}).hasOwnProperty.call(drupalSettings.aura.user_details, 'loyaltyStatus')) {
      const { loyaltyStatus } = drupalSettings.aura.user_details;
      this.state = {
        loyaltyStatus,
      };
    }
  }

  handleNotYou = (e) => {
    // @TODO: Discuss/Update the status value when user clicked on Not You?.
    const loyaltyStatus = 0;
    // API call to update user's loyalty status.
    const apiUrl = 'get/loyalty-club/apc-status-update?uid=' + drupalSettings.aura.user_details.id + '&apcLinkStatus=' + loyaltyStatus;
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
    let { loyaltyStatus } = this.state;
    loyaltyStatus = parseInt(loyaltyStatus, 10);

    if (loyaltyStatus === 0) {
      return <CardNotLinkedNoData />;
    } if (loyaltyStatus === 1) {
      return (
        <CardNotLinkedMdcData
          handleNotYou={this.handleNotYou}
        />
      );
    } if (loyaltyStatus === 3) {
      return <PendingFullEnrollment />;
    }

    return (null);
  }
}
