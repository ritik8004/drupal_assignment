import React from 'react';
import LoyaltyClubBlock from '../loyalty-club/loyalty-club-block';
import { getUserAuraStatus, getUserAuraTier, getAllAuraStatus } from '../../utilities/helper';

class MyAccount extends React.Component {
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
    const {
      loyaltyStatus,
    } = this.state;

    if (loyaltyStatus === getAllAuraStatus().APC_NOT_LINKED_NOT_U) {
      this.setState({
        wait: false,
      });
      return;
    }

    document.addEventListener('customerDetailsFetched', this.setCustomerDetails, false);
  }

  setCustomerDetails = (customerDetails) => {
    if (customerDetails.detail === null) {
      this.setState({
        wait: false,
      });
      return;
    }
    const {
      loyaltyStatus,
      tier,
      points,
      cardNumber,
      expiringPoints,
      expiryDate,
      pointsOnHold,
    } = this.state;

    const {
      auraStatus,
      tier: auraTier,
      auraPoints,
      cardNumber: auraCardNumber,
      auraPointsToExpire,
      auraPointsExpiryDate,
      auraOnHoldPoints,
    } = customerDetails.detail;

    this.setState({
      wait: false,
      loyaltyStatus: auraStatus || loyaltyStatus,
      tier: auraTier || tier,
      points: auraPoints || points,
      cardNumber: auraCardNumber || cardNumber,
      expiringPoints: auraPointsToExpire || expiringPoints,
      expiryDate: auraPointsExpiryDate || expiryDate,
      pointsOnHold: auraOnHoldPoints || pointsOnHold,
    });
  };

  updateLoyaltyStatus = (loyaltyStatus) => {
    this.setState({
      loyaltyStatus,
    });
  };

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

    return (
      <LoyaltyClubBlock
        wait={wait}
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
