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
    document.addEventListener('helloMemberPointsLoaded', this.eventListener, false);
  }

  componentWillUnmount() {
    this.isComponentMounted = false;
    document.removeEventListener('helloMemberPointsLoaded', this.eventListener, false);
  }

  eventListener = (e) => {
    const data = e.detail;

    // If no error from MDC.
    if (hasValue(data) && !hasValue(data.error)) {
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
          expiryInfo={customerData.extension_attributes.member_points_earned}
        />
        <PointsInfoSummary
          expiryInfo={customerData.extension_attributes.member_points_earned}
          pointsSummaryInfo={customerData.extension_attributes.member_points_info}
        />
      </>
    );
  }
}

export default MemberPointsSummary;
