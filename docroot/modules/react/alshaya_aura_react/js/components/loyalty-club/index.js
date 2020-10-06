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

  render() {
    const { loyaltyStatus } = this.state;

    return (
      <>
        <LoyaltyClubBlock loyaltyStatus={loyaltyStatus} />
        <LoyaltyClubTabs loyaltyStatus={loyaltyStatus} />
      </>
    );
  }
}

export default LoyaltyClub;
