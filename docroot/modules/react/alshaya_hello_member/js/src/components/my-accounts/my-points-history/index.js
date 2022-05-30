import React from 'react';

class MyPointsHistory extends React.Component {
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
      <>
        <div className="my-points-history-wrapper">
          <div className="purchase-store">
            <p className="history-dark-title">Instore Purchase</p>
            <p className="history-light-title">Lead Trinity</p>
          </div>
          <div className="points-date">02/12/2021</div>
          <div className="points-earned">
            <p className="history-light-title">Points earned</p>
            <p>124</p>
          </div>
          <div className="voucher-redeem">
            <p className="history-light-title">Voucher redeemed</p>
            <p>2</p>
          </div>
        </div>
      </>
    );
  }
}

export default MyPointsHistory;
