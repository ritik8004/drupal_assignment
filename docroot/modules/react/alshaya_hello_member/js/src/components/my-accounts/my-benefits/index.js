import React from 'react';
import MyOffersAndVouchers from './my-offers-vouchers';
import MyPoints from './my-points';

class MyBenefits extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      isMembershipStatus: true,
    };
  }

  render() {
    const {
      isMembershipStatus,
    } = this.state;

    if (!isMembershipStatus) {
      return {};
    }

    return (
      <div className="my-benefits-wrapper">
        <MyOffersAndVouchers />
        <MyPoints />
      </div>
    );
  }
}

export default MyBenefits;
