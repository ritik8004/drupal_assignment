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

    const expiryInfoData = JSON.parse(customerData.member_points_earned);

    return (
      <>
        <MembershipExpiryInfo
          pointTotal={expiryInfoData.total}
          expiryDate={expiryInfoData.expiry_date}
        />
        <PointsInfoSummary
          pointTotal={expiryInfoData.total}
          purchasePoints={expiryInfoData.purchase}
          pointsSummary={customerData.member_points_info}
        />
      </>
    );
  }
}

export default MemberPointsSummary;
