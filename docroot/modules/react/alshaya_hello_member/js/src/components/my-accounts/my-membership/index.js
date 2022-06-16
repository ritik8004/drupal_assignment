import React from 'react';
import QrCodeDisplay from './qr-code-display';
import Loading from '../../../../../../js/utilities/loading';
import { getFormatedMemberId } from '../../../utilities';
import getStringMessage from '../../../../../../js/utilities/strings';
import { hasValue } from '../../../../../../js/utilities/conditionsUtility';
import { getApcCustomerData } from '../../../hello_member_api_helper';
import TierProgress from './tier-progress';

class MyMembership extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      wait: false,
      myMembershipData: null,
    };
  }

  componentDidMount() {
    const apcCustomerData = getApcCustomerData();
    if (apcCustomerData instanceof Promise) {
      apcCustomerData.then((response) => {
        if (hasValue(response) && hasValue(response.data)) {
          this.setState({
            wait: true,
            myMembershipData: response.data,
          });
        }
      });
    }
  }

  render() {
    const { wait, myMembershipData } = this.state;

    if (!wait && myMembershipData === null) {
      return (
        <div className="membership-summary-wrapper" style={{ animationDelay: '0.4s' }}>
          <Loading />
        </div>
      );
    }

    const memberId = getFormatedMemberId(myMembershipData.apc_identifier_number);

    return (
      <>
        <div className="member-name">
          {getStringMessage('hi')}
          {' '}
          {myMembershipData.apc_first_name}
          {' '}
          {myMembershipData.apc_last_name}
        </div>
        <div className="points-block">
          <div className="my-points">
            <span>{myMembershipData.apc_points}</span>
            <span>{getStringMessage('points_label')}</span>
          </div>
          <TierProgress
            myMembershipData={myMembershipData}
          />
          <QrCodeDisplay memberId={memberId} />
          <div className="my-membership-id">
            {memberId}
          </div>
        </div>
      </>
    );
  }
}

export default MyMembership;
