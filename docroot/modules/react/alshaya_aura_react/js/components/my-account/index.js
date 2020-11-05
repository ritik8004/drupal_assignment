import React from 'react';
import LoyaltyClubBlock from '../loyalty-club/loyalty-club-block';
import { getAllAuraStatus } from '../../utilities/helper';
import dispatchCustomEvent from '../../../../js/utilities/events';
import { getAuraDetailsDefaultState } from '../../utilities/aura_utils';

class MyAccount extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      wait: true,
      ...getAuraDetailsDefaultState(),
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
    let stateValues = {
      loyaltyStatus,
    };

    if (loyaltyStatus === getAllAuraStatus().APC_NOT_LINKED_NOT_U) {
      stateValues = {
        ...getAuraDetailsDefaultState(),
        loyaltyStatus,
        signUpComplete: false,
      };
    }

    this.setState(stateValues);

    dispatchCustomEvent('loyaltyStatusUpdatedFromLoyaltyBlock', { stateValues });
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
