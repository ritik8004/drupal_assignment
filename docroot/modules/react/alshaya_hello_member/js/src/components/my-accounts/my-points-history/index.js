import React from 'react';
import ConditionalView from '../../../../../../js/utilities/components/conditional-view';
import { hasValue } from '../../../../../../js/utilities/conditionsUtility';
import logger from '../../../../../../js/utilities/logger';
import { removeFullScreenLoader, showFullScreenLoader } from '../../../../../../js/utilities/showRemoveFullScreenLoader';
import getStringMessage from '../../../../../../js/utilities/strings';
import { getApcPointsHistory } from '../../../hello_member_api_helper';
import { formatDate, getPointstHistoryPageSize } from '../../../utilities';

class MyPointsHistory extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      pointsHistoryData: [],
      pageSize: getPointstHistoryPageSize(),
      firstPage: 1,
      totalCount: null,
    };
    this.loadMore = this.loadMore.bind(this);
  }

  componentDidMount() {
    this.getPointsHistoryData();
  }

  loadMore() {
    const { pageSize } = this.state;
    this.setState((prev) => ({ firstPage: prev.firstPage + pageSize }), () => {
      this.getPointsHistoryData();
    });
  }

  getPointsHistoryData() {
    const { firstPage, pageSize, pointsHistoryData } = this.state;
    showFullScreenLoader();
    const apcPointsHistoryData = getApcPointsHistory(firstPage, pageSize);
    if (apcPointsHistoryData instanceof Promise) {
      apcPointsHistoryData.then((response) => {
        if (hasValue(response) && !hasValue(response.error) && hasValue(response.data)) {
          this.setState({
            pointsHistoryData: pointsHistoryData.concat(response.data.apc_transactions),
            totalCount: response.data.apc_transactions.length,
          });
        } else if (hasValue(response.error)) {
          logger.error('Error while trying to get apc points history data. Data: @data.', {
            '@data': JSON.stringify(response),
          });
        }
        removeFullScreenLoader();
      });
    }
  }

  render() {
    const { pointsHistoryData, totalCount, pageSize } = this.state;

    if (pointsHistoryData === null) {
      return null;
    }

    return (
      <>
        <div className="my-points-history-wrapper">
          {pointsHistoryData.map((data) => (
            <div className="history-points-row" key={data.trn_no}>
              <div className="purchase-store">
                <p className="history-dark-title">
                  {data.channel}
                </p>
                <ConditionalView condition={hasValue(data.location_name)}>
                  <p className="history-light-title">{data.location_name}</p>
                </ConditionalView>
              </div>
              <div className="points-date">{formatDate(data.date)}</div>
              <div className="points-earned">
                <p className="history-light-title">{getStringMessage('points_earned')}</p>
                <p>{data.points}</p>
              </div>
            </div>
          ))}
          <ConditionalView condition={totalCount === pageSize}>
            <div className="load-more-wrapper">
              <button onClick={this.loadMore} type="button" className="load-more">{getStringMessage('load_more')}</button>
            </div>
          </ConditionalView>
        </div>
      </>
    );
  }
}

export default MyPointsHistory;
