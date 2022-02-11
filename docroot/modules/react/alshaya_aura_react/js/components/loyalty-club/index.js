import React from 'react';
import LoyaltyClubBlock from './loyalty-club-block';
import LoyaltyClubTabs from './loyalty-club-tabs';
import { getAllAuraStatus } from '../../utilities/helper';
import dispatchCustomEvent from '../../../../js/utilities/events';
import { getAuraDetailsDefaultState } from '../../utilities/aura_utils';
import { isUserAuthenticated } from '../../../../js/utilities/helper';

class LoyaltyClub extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      wait: true,
      ...getAuraDetailsDefaultState(),
      notYouFailed: false,
      linkCardFailed: false,
    };
  }

  componentDidMount() {
    const {
      loyaltyStatus,
    } = this.state;

    document.addEventListener('customerDetailsFetched', this.setCustomerDetails, false);
    document.addEventListener('loyaltyStatusUpdated', this.setCustomerDetails, false);

    if (loyaltyStatus === getAllAuraStatus().APC_NOT_LINKED_NOT_U) {
      this.setState({
        wait: false,
      });
    }

    // Set wait false for guest user.
    if (!isUserAuthenticated() && loyaltyStatus === 0) {
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

    dispatchCustomEvent('loyaltyStatusUpdated', { stateValues });
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
      firstName,
      lastName,
      notYouFailed,
      linkCardFailed,
    } = this.state;

    return (
      <>
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
          firstName={firstName}
          lastName={lastName}
          notYouFailed={notYouFailed}
          linkCardFailed={linkCardFailed}
          updateLoyaltyStatus={this.updateLoyaltyStatus}
        />
        <LoyaltyClubTabs loyaltyStatus={loyaltyStatus} />
      </>
    );
  }
}

export default LoyaltyClub;
