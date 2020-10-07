import React from 'react';
import LoyaltyClubBlock from './loyalty-club-block';
import LoyaltyClubTabs from './loyalty-club-tabs';
import { getUserAuraStatus } from '../../utilities/helper';

class LoyaltyClub extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      loyaltyStatus: getUserAuraStatus(),
    };
  }

  updateLoyaltyStatus = (loyaltyStatus) => {
    this.setState({
      loyaltyStatus,
    });
  }

  render() {
    const { loyaltyStatus } = this.state;

    return (
      <>
        <LoyaltyClubBlock
          loyaltyStatus={loyaltyStatus}
          updateLoyaltyStatus={this.updateLoyaltyStatus}
        />
        <LoyaltyClubTabs loyaltyStatus={loyaltyStatus} />
      </>
    );
  }
}

export default LoyaltyClub;
