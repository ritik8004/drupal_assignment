import React from 'react';
import Select from 'react-select';
import { getAPIData } from '../../../utilities/api/fetchApiData';
import { getUserDetails, getAuraConfig } from '../../../utilities/helper';
import {
  addInlineLoader,
  removeInlineLoader,
} from '../../../utilities/aura_utils';
import {
  getTransactionTypeOptions,
  getTransactionDateOptions,
  formatDate,
  getTransactionDateOptionsDefaultValue,
} from '../../../utilities/reward_activity_helper';
import Loading from '../../../../../alshaya_spc/js/utilities/loading';
import EmptyRewardActivity from './empty-reward-activity';

class LoyaltyClubRewardsActivity extends React.Component {
  constructor(props) {
    super(props);
    this.typeSelectRef = React.createRef();
    this.dateSelectRef = React.createRef();
    this.state = {
      activity: null,
      dateFilterOptions: getTransactionDateOptions(),
      fromDate: '',
      toDate: '',
      type: '',
      wait: true,
      noStatement: false,
    };
  }

  componentDidMount() {
    // Getting user's last reward transaction details.
    // Api doesn't require from/to date if we need last transaction details
    // and thus passing empty params for dates.
    this.fetchRewardActivity('', '', 1, '');
  }

  fetchRewardActivity = (fromDate = '', toDate = '', maxResults = 0, channel = '') => {
    addInlineLoader('.reward-activity');
    // API call to get reward activity for logged in users.
    const { rewardActivityTimeLimit } = getAuraConfig();
    const apiUrl = `get/loyalty-club/get-reward-activity?uid=${getUserDetails().id}&fromDate=${fromDate}&toDate=${toDate}&maxResults=${maxResults}&channel=${channel}&duration=${rewardActivityTimeLimit}`;
    const apiData = getAPIData(apiUrl);

    if (apiData instanceof Promise) {
      apiData.then((result) => {
        let statement = null;
        if (result.data !== undefined && result.data.error === undefined) {
          this.setState({
            activity: result.data.data || null,
            wait: false,
          });
          this.setFromAndToDate(result.data.data);

          statement = result.data.data;
        }

        if (Array.isArray(statement) && statement.length === 0) {
          this.setState({
            noStatement: true,
          });
        }
        removeInlineLoader('.reward-activity');
      });
    }
  };

  setFromAndToDate = (activity) => {
    if (activity === null || Object.entries(activity).length === 0) {
      return;
    }
    const date = new Date(Object.entries(activity)[0][1].date);

    this.setState({
      fromDate: formatDate(new Date(date.getFullYear(), date.getMonth()), 'YYYY-MM-DD'),
      toDate: formatDate(new Date(date.getFullYear(), date.getMonth() + 1, 0), 'YYYY-MM-DD'),
    });
  };

  generateStatement = () => {
    const { activity } = this.state;
    if (activity === null || activity === 'undefined') {
      return null;
    }

    const statement = [];

    // Check for empty reward activity.
    if (Array.isArray(activity) && activity.length === 0) {
      statement.push(
        <div className="no-reward-activity">
          <EmptyRewardActivity />
        </div>,
      );

      removeInlineLoader('.reward-activity');
      return statement;
    }

    Object.entries(activity).forEach(([, transaction]) => {
      const date = new Date(transaction.date).toLocaleString(
        'default',
        { day: 'numeric', month: 'short', year: 'numeric' },
      );
      statement.push(
        <div className="statement-row">
          <span className="order-id">{transaction.orderNo}</span>
          <span className="date">{date}</span>
          <span className="amount">{`${transaction.currencyCode} ${transaction.orderTotal}`}</span>
          <span className="type">{transaction.channel}</span>
          <span className={`aura-points style-${transaction.status}`}>{transaction.auraPoints}</span>
          <span className={`status style-${transaction.status}`}>{transaction.statusName}</span>
        </div>,
      );
    });

    removeInlineLoader('.reward-activity');

    return statement;
  };

  onMenuOpen = (filterName) => {
    if (filterName === 'date') {
      this.dateSelectRef.current.select.inputRef.closest('.reward-activity-filter').classList.add('open');
    }

    if (filterName === 'type') {
      this.typeSelectRef.current.select.inputRef.closest('.reward-activity-filter').classList.add('open');
    }
  };

  onMenuClose = (filterName) => {
    if (filterName === 'date') {
      this.dateSelectRef.current.select.inputRef.closest('.reward-activity-filter').classList.remove('open');
    }
    if (filterName === 'type') {
      this.typeSelectRef.current.select.inputRef.closest('.reward-activity-filter').classList.remove('open');
    }
  };

  handleTypeChange = (selectedOption) => {
    const { fromDate, toDate } = this.state;
    const type = selectedOption.value !== 'all'
      ? selectedOption.value
      : '';

    this.fetchRewardActivity(fromDate, toDate, 0, type);
    this.setState({
      type,
    });
  };

  handleDateChange = (selectedOption) => {
    const date = new Date(selectedOption.value);
    const fromDate = formatDate(date, 'YYYY-MM-DD');
    const toDate = formatDate(new Date(date.getFullYear(), date.getMonth() + 1, 0), 'YYYY-MM-DD');
    const { type } = this.state;

    this.fetchRewardActivity(fromDate, toDate, 0, type);
    this.setState({
      fromDate,
      toDate,
    });
  };

  render() {
    const {
      activity,
      dateFilterOptions,
      wait,
      noStatement,
      fromDate,
    } = this.state;
    const transactionTypeOptions = getTransactionTypeOptions();

    let rewardStatementClass = 'reward-activity';
    if (noStatement === true) {
      rewardStatementClass = 'empty-reward-activity';
    }

    if (wait) {
      return (
        <div className="loyalty-club-rewards-wrapper loyalty-tab-content fadeInUp" style={{ animationDelay: '0.6s' }}>
          <Loading />
        </div>
      );
    }

    return (
      <div className="loyalty-club-rewards-wrapper loyalty-tab-content fadeInUp" style={{ animationDelay: '0.6s' }}>
        <div className="filters">
          <Select
            ref={this.dateSelectRef}
            classNamePrefix="spcAuraSelect"
            className="reward-activity-filter transaction-date-filter"
            name="transactionDateFilter"
            onMenuOpen={() => this.onMenuOpen('date')}
            onMenuClose={() => this.onMenuClose('date')}
            options={dateFilterOptions}
            defaultValue={dateFilterOptions[0]}
            value={getTransactionDateOptionsDefaultValue(fromDate)}
            onChange={this.handleDateChange}
            isSearchable={false}
            key="date-filter"
          />
          <Select
            ref={this.typeSelectRef}
            classNamePrefix="spcAuraSelect"
            className="reward-activity-filter transaction-type-filter"
            name="transactionTypeFilter"
            onMenuOpen={() => this.onMenuOpen('type')}
            onMenuClose={() => this.onMenuClose('type')}
            options={transactionTypeOptions}
            defaultValue={transactionTypeOptions[0]}
            onChange={this.handleTypeChange}
            isSearchable={false}
            key="transaction-filter"
          />
        </div>
        {noStatement === false
        && (
          <div className="header-row">
            <span className="order-id">{Drupal.t('Order No.')}</span>
            <span className="date">{Drupal.t('Date')}</span>
            <span className="amount">{Drupal.t('Order Total')}</span>
            <span className="type">{Drupal.t('Online / Offline')}</span>
            <span className="aura-points">{Drupal.t('AURA points')}</span>
            <span className="status">{Drupal.t('Status')}</span>
          </div>
        )}

        <div className={rewardStatementClass}>
          {this.generateStatement()}
        </div>
      </div>
    );
  }
}

export default LoyaltyClubRewardsActivity;
