import React from 'react';
import Loading from '../../../../../../js/utilities/loading';
import getStringMessage from '../../../../../../js/utilities/strings';
import MembershipExpiryInfo from './membership-expiry-info';
import PointsInfoSummary from './membership-expiry-points';

class MyPointsHistory extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      wait: false,
      pointsHistoryData: null,
    };
  }

  async componentDidMount() {
    // --TODO-- API integration task to be started once we have api from MDC.
    const pointsHistoryData = {
      apc_transactions: [
        {
          location_name: 'lead trinity',
          partner: 'HEN',
          currency_code: 'KWD',
          date: '2022-05-23T12:38:25Z',
          identifier_no: '6111000000065683',
          points: '2',
          trn_no: '16534031963700',
          channel: 'Online',
          transaction_id: '5274981',
          points_balances: [
            {
              point_type: 'XP',
              points: '2',
              status: 'B',
              status_name: 'Booked',
            },
          ],
        },
        {
          location_name: 'lead trinity',
          partner: 'BAT',
          currency_code: 'KWD',
          date: '2022-05-23T12:38:25Z',
          identifier_no: '6111000000065683',
          points: '2',
          trn_no: '16533787665520',
          channel: 'Instore',
          transaction_id: '5274982',
          points_balances: [
            {
              point_type: 'CR',
              points: '2',
              status: 'B',
              status_name: 'Booked',
            },
          ],
        },
      ],
      points_summary: {
        points_earned: {
          purchase: '30',
          rating_review: '20',
          profile_completion: '5',
          total_points: 55,
          expiry_date: '2024-05-23',
        },
        points_info: {
          currency_code: 'KWD',
          currency_value: '1',
          points_value: '5',
          rating_review: '5',
          profile_completion: '25',
        },
      },
      message: null,
      error: null,
    };

    this.setState({
      wait: true,
      pointsHistoryData,
    });
  }

  render() {
    const { wait, pointsHistoryData } = this.state;

    if (!wait && pointsHistoryData === null) {
      return (
        <div className="my-points-history-wrapper" style={{ animationDelay: '0.4s' }}>
          <Loading />
        </div>
      );
    }

    return (
      <>
        <div className="my-points-history-wrapper">
          {pointsHistoryData.apc_transactions.map((data) => (
            <div className="history-points-row" key={data.transaction_id}>
              <div className="purchase-store">
                <p className="history-dark-title">
                  {data.channel}
                  {' '}
                  {getStringMessage('purchase')}
                </p>
                <p className="history-light-title">{data.location_name}</p>
              </div>
              <div className="points-date">{data.date}</div>
              <div className="points-earned">
                <p className="history-light-title">{getStringMessage('points_earned')}</p>
                <p>{data.points}</p>
              </div>
            </div>
          ))}
        </div>
        <MembershipExpiryInfo expiryInfo={pointsHistoryData.points_summary.points_earned} />
        <PointsInfoSummary pointsSummaryInfo={pointsHistoryData.points_summary} />
      </>
    );
  }
}

export default MyPointsHistory;
