import React from 'react';
import LoyaltyClubBlock from './loyalty-club-block';
import LoyaltyClubTabs from './loyalty-club-tabs';
import { getUserAuraStatus, getUserAuraTier } from '../../utilities/helper';
import { getAPIData } from '../../utilities/api/fetchApiData';
import {
  showFullScreenLoader,
  removeFullScreenLoader,
} from '../../../../alshaya_spc/js/utilities/checkout_util';

class LoyaltyClub extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      wait: true,
      loyaltyStatus: getUserAuraStatus(),
      tier: getUserAuraTier(),
      points: 0,
      cardNumber: '',
      pointsOnHold: 0,
      upgradeMsg: '',
      expiringPoints: 0,
      expiryDate: '',
    };
  }

  componentDidMount() {
    // API call to get customer points.
    const {
      loyaltyStatus,
      tier,
      points,
      cardNumber,
      expiringPoints,
      expiryDate,
      pointsOnHold,
    } = this.state;

    const apiUrl = `get/loyalty-club/get-customer-details?tier=${tier}&status=${loyaltyStatus}`;
    const apiData = getAPIData(apiUrl);
    showFullScreenLoader();

    if (apiData instanceof Promise) {
      apiData.then((result) => {
        if (result.data !== undefined && result.data.error === undefined) {
          this.setState({
            wait: false,
            loyaltyStatus: result.data.auraStatus || loyaltyStatus,
            tier: result.data.tier || tier,
            points: result.data.auraPoints || points,
            cardNumber: result.data.cardNumber || cardNumber,
            expiringPoints: result.data.auraPointsToExpire || expiringPoints,
            expiryDate: result.data.auraPointsExpiryDate || expiryDate,
            pointsOnHold: result.data.auraOnHoldPoints || pointsOnHold,
          });
        }
        removeFullScreenLoader();
      });
    }
  }

  updateLoyaltyStatus = (loyaltyStatus) => {
    this.setState({
      loyaltyStatus,
    });
  }

  render() {
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
    } = this.state;

    if (wait) {
      return null;
    }

    return (
      <>
        <LoyaltyClubBlock
          loyaltyStatus={loyaltyStatus}
          tier={tier}
          points={points}
          cardNumber={cardNumber}
          expiringPoints={expiringPoints}
          expiryDate={expiryDate}
          pointsOnHold={pointsOnHold}
          upgradeMsg={upgradeMsg}
          updateLoyaltyStatus={this.updateLoyaltyStatus}
        />
        <LoyaltyClubTabs loyaltyStatus={loyaltyStatus} />
      </>
    );
  }
}

export default LoyaltyClub;
