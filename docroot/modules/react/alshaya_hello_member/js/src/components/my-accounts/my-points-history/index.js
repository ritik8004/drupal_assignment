import React from 'react';
import moment from 'moment';
import { hasValue } from '../../../../../../js/utilities/conditionsUtility';
import logger from '../../../../../../js/utilities/logger';
import getStringMessage from '../../../../../../js/utilities/strings';
import { getHelloMemberPointsHistory } from '../../../hello_member_api_helper';
import { getPointstHistoryPageSize } from '../../../utilities';
import MemberPointsSummary from './member-points-summary';
import { removeFullScreenLoader, showFullScreenLoader } from '../../../../../../js/utilities/showRemoveFullScreenLoader';

class MyPointsHistory extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      pointsHistoryData: [],
      pageSize: getPointstHistoryPageSize(),
      firstPage: 1,
      totalCount: 0,
    };
  }

  componentDidMount() {
    // Get transactions data purchased via hello member points.
    this.getPointsHistoryData();
  }

  /**
   * Load points history data for next page when user clicks on load more.
   */
  loadMore = () => {
    const { pageSize } = this.state;
    this.setState((prev) => ({ firstPage: prev.firstPage + pageSize }), () => {
      this.getPointsHistoryData();
    });
  }

  /**
   * Fetch all the points history data for purchase done via hello member points.
   */
  getPointsHistoryData = () => {
    const { firstPage, pageSize, pointsHistoryData } = this.state;
    showFullScreenLoader();
    const helloMemberPointsHistoryData = getHelloMemberPointsHistory(firstPage, pageSize);
    if (helloMemberPointsHistoryData instanceof Promise) {
      helloMemberPointsHistoryData.then((response) => {
        if (hasValue(response) && !hasValue(response.error) && hasValue(response.data)) {
          if (hasValue(response.data.apc_transactions)) {
            this.setState({
              pointsHistoryData: pointsHistoryData.concat(response.data.apc_transactions),
              totalCount: response.data.apc_transactions.length,
            });
          } else {
            this.setState({
              totalCount: 0,
            });
          }
        } else if (hasValue(response.error)) {
          logger.error('Error while trying to get hello member points history data. Data: @data.', {
            '@data': JSON.stringify(response),
          });
        }
        removeFullScreenLoader();
      });
    }
  }

  render() {
    const {
      pointsHistoryData, totalCount, pageSize,
    } = this.state;

    if (pointsHistoryData === null) {
      return null;
    }

    return (
      <>
        <MemberPointsSummary />
        <div className="my-points-history-wrapper">
          {pointsHistoryData.map((data) => (
            <div className="history-points-row" key={data.trn_no}>
              <div className="purchase-store">
                <p className="history-dark-title">
                  {data.channel}
                </p>
                {hasValue(data.location_name) && (
                  <p className="history-light-title">{data.location_name}</p>
                )}
              </div>
              <div className="points-date">{moment(new Date(data.date)).format('DD/MM/YYYY')}</div>
              <div className="points-earned">
                <p className="history-light-title">{getStringMessage('points_earned')}</p>
                <p>{data.points}</p>
              </div>
            </div>
          ))}
          {(totalCount === pageSize) && (
            <div className="load-more-wrapper">
              <button onClick={this.loadMore} type="button" className="load-more">{getStringMessage('load_more')}</button>
            </div>
          )}
        </div>
      </>
    );
  }
}

export default MyPointsHistory;
