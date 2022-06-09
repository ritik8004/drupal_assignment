import React from 'react';
import TierProgress from './tier-progress';
import QrCode from './qr-code';
import Loading from '../../../../../../js/utilities/loading';
import { getFormatedMemberId, getPointsData } from '../../../utilities';
import getStringMessage from '../../../../../../js/utilities/strings';

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
    const memberData = {
      apc_identifier_number: '6111000000021975',
      apc_link: 2,
      apc_points: 225,
      apc_link_date: '2021-12-06',
      apc_phone_number: '+97455555555',
      apc_first_name: 'test',
      apc_last_name: 'comarch01',
      is_new_customer: 1,
      member_qr_code: '',
      member_points_info: [
        {
          code: 'GET_PLUS',
          value: '750',
        },
        {
          code: 'NEW_VOUCHER',
          value: '250',
        },
        {
          code: 'POINTS_GATHERED',
          value: '250',
        },
        {
          code: 'PROLONG_PLUS',
          value: '0',
        },
        {
          code: 'GET_PLUS_ONE',
          value: '12500',
        },
      ],
      current_tier: 'Hello',
      next_tier: 'Plus',
      points_summary: 'Your are 250 points away from getting your next bonus voucher and 750 points away to become a plus member. Vouchers have a 30-day delay',
      message: null,
      error: null,
    };

    this.setState({
      wait: true,
      myMembershipData: memberData,
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
    const pointsData = getPointsData(myMembershipData.current_tier,
      myMembershipData.member_points_info);
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
            <span>{pointsData.pointsGathered}</span>
            <span>{getStringMessage('points_label')}</span>
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
