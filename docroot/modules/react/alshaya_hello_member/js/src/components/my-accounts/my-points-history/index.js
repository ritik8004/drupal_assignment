import React from 'react';
import moment from 'moment';
import ConditionalView from '../../../../../../js/utilities/components/conditional-view';
import { hasValue } from '../../../../../../js/utilities/conditionsUtility';
import logger from '../../../../../../js/utilities/logger';
import { removeFullScreenLoader, showFullScreenLoader } from '../../../../../../js/utilities/showRemoveFullScreenLoader';
import getStringMessage from '../../../../../../js/utilities/strings';
import { getHelloMemberPointsHistory } from '../../../hello_member_api_helper';
import { getPointstHistoryPageSize } from '../../../utilities';
import MembershipExpiryInfo from './membership-expiry-info';
import PointsInfoSummary from './membership-expiry-points';

class MyPointsHistory extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      pointsHistoryData: [],
      pageSize: getPointstHistoryPageSize(),
      firstPage: 1,
      totalCount: 0,
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
      showFullScreenLoader();
      // Get transactions data purchased via hello member points.
      this.getPointsHistoryData();
    }
  };

  /**
   * Load points history data for next page when user clicks on load more.
   */
  loadMore = () => {
    const { pageSize } = this.state;
    this.setState((prev) => ({ firstPage: prev.firstPage + pageSize }), () => {
      showFullScreenLoader();
      this.getPointsHistoryData();
    });
  }

  /**
   * Fetch all the points history data for purchase done via hello member points.
   */
  getPointsHistoryData = () => {
    const { firstPage, pageSize, pointsHistoryData } = this.state;
    const hmPointsHistoryData = getHelloMemberPointsHistory(firstPage, pageSize);
    if (hmPointsHistoryData instanceof Promise) {
      hmPointsHistoryData.then((response) => {
        if (hasValue(response) && !hasValue(response.error) && hasValue(response.data)) {
          this.setState({
            pointsHistoryData: pointsHistoryData.concat(response.data.apc_transactions),
            totalCount: response.data.apc_transactions.length,
          });
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
      pointsHistoryData, totalCount, pageSize, customerData,
    } = this.state;
    if (pointsHistoryData === null || customerData === null) {
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
              <div className="points-date">{moment(new Date(data.date)).format('DD/MM/YYYY')}</div>
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
          <MembershipExpiryInfo
            expiryInfo={customerData.extension_attributes.member_points_earned}
          />
          <PointsInfoSummary
            expiryInfo={customerData.extension_attributes.member_points_earned}
            pointsSummaryInfo={customerData.extension_attributes.member_points_info}
          />
        </div>
      </>
    );
  }
}

export default MyPointsHistory;
