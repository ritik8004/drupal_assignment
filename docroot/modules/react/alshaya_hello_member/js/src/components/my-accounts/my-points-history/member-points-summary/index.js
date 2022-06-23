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
    this.isComponentMounted = true;
    // Listen to `helloMemberPointsLoaded` event which will update points summary block.
    document.addEventListener('helloMemberPointsLoaded', this.updatePointSummary, false);
  }

  componentWillUnmount() {
    this.isComponentMounted = false;
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

    return (
      <>
        <MembershipExpiryInfo
          expiryInfo={customerData.member_points_earned}
        />
        <PointsInfoSummary
          expiryInfo={customerData.member_points_earned}
          pointsSummary={customerData.member_points_info}
        />
      </>
    );
  }
}

export default MemberPointsSummary;
