import React from 'react';
import LoyaltyClubBlock from '../loyalty-club/loyalty-club-block';
import { getUserAuraStatus } from '../../utilities/helper';

class MyAccount extends React.Component {
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
      <LoyaltyClubBlock
        loyaltyStatus={loyaltyStatus}
        updateLoyaltyStatus={this.updateLoyaltyStatus}
      />
    );
  }
}

export default MyAccount;
