import React from 'react';
import { hasValue } from '../../../../../../../js/utilities/conditionsUtility';
import Loading from '../../../../../../../js/utilities/loading';
import MembershipExpiryInfo from '../membership-expiry-info';
import PointsInfoSummary from '../membership-expiry-points';

class MemberPointsSummary extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      customerData: null,
      wait: false,
    };
  }

  componentDidMount() {
    // Listen to `helloMemberPointsLoaded` event which will update points summary block.
    document.addEventListener('helloMemberPointsLoaded', this.updatePointSummary, false);
  }

  componentWillUnmount() {
    document.removeEventListener('helloMemberPointsLoaded', this.updatePointSummary, false);
  }

  updatePointSummary = (e) => {
    const data = e.detail;

    if (hasValue(data)) {
      this.setState({
        customerData: data,
        wait: true,
      });
    }
  };

  render() {
    const { wait, customerData } = this.state;

    if (!wait) {
      return (
        <div className="my-points-history-wrapper" style={{ animationDelay: '0.4s' }}>
          <Loading />
        </div>
      );
    }

    if (customerData === null) {
      return null;
    }

    if (!hasValue(customerData.member_points_earned)
      || !hasValue(customerData.member_points_info)) {
      return null;
    }

    return (
      <>
        <MembershipExpiryInfo
          pointTotal={customerData.member_points_earned.total}
          expiryDate={customerData.member_points_earned.expiry_date}
        />
        <PointsInfoSummary
          pointsEarned={customerData.member_points_earned}
          pointsSummary={customerData.member_points_info}
        />
      </>
    );
  }
}

export default MemberPointsSummary;
