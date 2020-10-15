import React from 'react';
import LoyaltyClubBlock from '../loyalty-club/loyalty-club-block';
import { getUserAuraStatus, getUserAuraTier } from '../../utilities/helper';
import { getAPIData } from '../../utilities/api/fetchApiData';

class MyAccount extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
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
      loyaltyStatus, tier, points, cardNumber, expiringPoints, expiryDate, pointsOnHold,
    } = this.state;
    const apiUrl = `get/loyalty-club/get-customer-details?tier=${tier}&status=${loyaltyStatus}`;
    const apiData = getAPIData(apiUrl);

    if (apiData instanceof Promise) {
      apiData.then((result) => {
        if (result.data !== undefined && result.data.error === undefined) {
          this.setState({
            loyaltyStatus: result.data.auraStatus || loyaltyStatus,
            tier: result.data.tier || tier,
            points: result.data.auraPoints || points,
            cardNumber: result.data.cardNumber || cardNumber,
            expiringPoints: result.data.auraPointsToExpire || expiringPoints,
            expiryDate: result.data.auraPointsExpiryDate || expiryDate,
            pointsOnHold: result.data.auraOnHoldPoints || pointsOnHold,
          });
        }
      });
    }
  }

  updateLoyaltyStatus = (loyaltyStatus) => {
    this.setState({
      loyaltyStatus,
    });
  };

  render() {
    const {
      loyaltyStatus, tier, points, cardNumber, expiringPoints, expiryDate, pointsOnHold, upgradeMsg,
    } = this.state;

    return (
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
    );
  }
}

export default MyAccount;
