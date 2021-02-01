import React from 'react';
import { getAPIData } from '../../../utilities/api/fetchApiData';
import { getUserDetails } from '../../../utilities/helper';
import { addInlineLoader } from '../../../utilities/aura_utils';

class LoyaltyClubRewardsActivity extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      activity: null,
    };
  }

  componentDidMount() {
    // @todo: Remove hard coded to and from date when last transaction API is ready.
    const fromDate = '2020-12-01';
    const toDate = '2021-12-31';

    this.fetchRewardActivity(fromDate, toDate, 0, '');
  }

  fetchRewardActivity = (fromDate, toDate, maxResults, channel) => {
    // API call to get reward activity for logged in users.
    const apiUrl = `get/loyalty-club/get-reward-activity?uid=${getUserDetails().id}&fromDate=${fromDate}&toDate=${toDate}&maxResults=${maxResults}&channel=${channel}`;
    const apiData = getAPIData(apiUrl);

    if (apiData instanceof Promise) {
      apiData.then((result) => {
        if (result.data !== undefined && result.data.error === undefined) {
          this.setState({
            activity: result.data.data || null,
          });
        }
      });
    }
  };

  generateStatement = () => {
    const { activity } = this.state;
    if (activity === null || activity === 'undefined') {
      addInlineLoader('.reward-activity');
      return null;
    }

    const statement = [];

    Object.entries(activity).forEach(([, transaction]) => {
      statement.push(
        <div className="statement-row">
          <span className="order-id">{transaction.orderNo}</span>
          <span className="date">{transaction.date}</span>
          <span className="amount">{transaction.orderTotal}</span>
          <span className="type">{transaction.channel}</span>
          <span className={`aura-points style-${transaction.status}`}>{transaction.auraPoints}</span>
          <span className={`status style-${transaction.status}`}>{transaction.status}</span>
        </div>,
      );
    });


    return statement;
  };

  render() {
    return (
      <div className="loyalty-club-rewards-wrapper loyalty-tab-content fadeInUp" style={{ animationDelay: '0.6s' }}>
        <div className="header-row">
          <span className="order-id">{Drupal.t('Order No.')}</span>
          <span className="date">{Drupal.t('Date')}</span>
          <span className="amount">{Drupal.t('Order Total')}</span>
          <span className="type">{Drupal.t('Online / Offline')}</span>
          <span className="aura-points">{Drupal.t('AURA points')}</span>
          <span className="status">{Drupal.t('Status')}</span>
        </div>
        <div className="reward-activity">
          {this.generateStatement()}
        </div>
      </div>
    );
  }
}

export default LoyaltyClubRewardsActivity;
