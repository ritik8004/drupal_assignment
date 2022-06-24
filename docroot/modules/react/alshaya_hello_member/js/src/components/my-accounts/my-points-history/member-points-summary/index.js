import React from 'react';
import { hasValue } from '../../../../../../../js/utilities/conditionsUtility';
import MembershipExpiryInfo from '../membership-expiry-info';
import PointsInfoSummary from '../membership-expiry-points';

class MemberPointsSummary extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      customerData: null,
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
      });
    }
  };

  render() {
    const { customerData } = this.state;
    if (customerData === null) {
      return null;
    }

    if (!hasValue(customerData.member_points_earned)
      || !hasValue(customerData.member_points_info)) {
      return null;
    }

    const memberPointsEarned = JSON.parse(customerData.member_points_earned);
    const memberPointsInfo = JSON.parse(customerData.member_points_info);

    return (
      <>
        <MembershipExpiryInfo
          pointTotal={memberPointsEarned.total}
          expiryDate={memberPointsEarned.expiry_date}
        />
        <PointsInfoSummary
          pointsEarned={memberPointsEarned}
          pointsSummary={memberPointsInfo}
        />
      </>
    );
  }
}

export default MemberPointsSummary;
