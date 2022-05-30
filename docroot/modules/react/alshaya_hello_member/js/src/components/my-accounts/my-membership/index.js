import React from 'react';
import TierProgress from './tier-progress';
import QrCode from './qr-code';
import MembershipData from './MembershipData.json';
import Loading from '../../../../../../js/utilities/loading';
import { getFormatedMemberId, getFullName } from '../../../utilities';

class MyMembership extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      wait: false,
      myMembershipData: null,
    };
  }

  async componentDidMount() {
    // --TODO-- API integration task to be started once we have api from MDC.
    this.setState({
      wait: true,
      myMembershipData: MembershipData,
    });
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
    const fullName = getFullName(myMembershipData.apc_first_name, myMembershipData.apc_last_name);
    return (
      <>
        <div className="member-name">
          {fullName}
        </div>
        <div className="points-block">
          <div className="my-points">
            {myMembershipData.apc_points}
            {' '}
            {' '}
            {' '}
            {Drupal.t('Points')}
          </div>
          <TierProgress
            currentTier={myMembershipData.current_tier}
            nextTier={myMembershipData.next_tier}
            memberPointsInfo={myMembershipData.member_points_info}
          />
          <div className="my-points-details">
            {myMembershipData.points_summary}
          </div>
          <QrCode qrImage={myMembershipData.member_qr_code} memberId={memberId} />
          <div className="my-membership-id">
            {memberId}
          </div>
        </div>
      </>
    );
  }
}

export default MyMembership;
