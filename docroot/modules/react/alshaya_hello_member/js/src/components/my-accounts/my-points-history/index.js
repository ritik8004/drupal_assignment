import React from 'react';
import MembershipExpiry from './membership-expiry';
import MembershipExpiryPoints from './membership-expiry-points';

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
          <div className="history-points-row">
            <div className="purchase-store">
              <p className="history-dark-title">{/* Instore Purchase */}</p>
              <p className="history-light-title">{/* Lead Trinity */}</p>
            </div>
            <div className="points-date">{/* date 02/12/2021 */}</div>
            <div className="points-earned">
              <p className="history-light-title">{/* Points earned */}</p>
              <p>{/* points eg:124 */}</p>
            </div>
            <div className="voucher-redeem">
              <p className="history-light-title">{/* Voucher redeemed */}</p>
              <p>{/* no of voucher 2 */}</p>
            </div>
          </div>
        </div>
        <MembershipExpiry />
        <MembershipExpiryPoints />
      </>
    );
  }
}

export default MyPointsHistory;
