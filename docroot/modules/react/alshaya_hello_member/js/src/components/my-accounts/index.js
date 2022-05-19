
import React from 'react';
import MembershipPopup from './membership-popup';
import MyBenefits from './my-benefits';
import MyPoints from './my-points';

class MyAccount extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      membershipStatus: false,
    };
  }

  componentDidMount() {
    this.setState({
      membershipStatus: false,
    });
  }

  render() {
    const {
      membershipStatus,
    } = this.state;

    return (
      <>
        <MembershipPopup
          membershipStatus={membershipStatus}
        />
        <MyBenefits />
        <MyPoints />
      </>
    );
  }
}

export default MyAccount;
