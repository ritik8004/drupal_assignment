import React from 'react';
import LoyaltyClubBlock from '../loyalty-club/loyalty-club-block';
import { getUserAuraStatus, getUserAuraTier, getAllAuraStatus } from '../../utilities/helper';
import dispatchCustomEvent from '../../../../js/utilities/events';

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

    document.addEventListener('customerDetailsFetched', this.setCustomerDetails, false);
    document.addEventListener('loyaltyStatusUpdatedFromHeader', this.setCustomerDetails, false);

    if (loyaltyStatus === getAllAuraStatus().APC_NOT_LINKED_NOT_U) {
      this.setState({
        wait: false,
      });
    }
  }

  setCustomerDetails = (data) => {
    const { stateValues } = data.detail;
    this.setState({
      ...stateValues,
    });
  };

  updateLoyaltyStatus = (loyaltyStatus) => {
    this.setState({
      loyaltyStatus,
    });
    dispatchCustomEvent('loyaltyStatusUpdatedFromLoyaltyBlock', loyaltyStatus);
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
