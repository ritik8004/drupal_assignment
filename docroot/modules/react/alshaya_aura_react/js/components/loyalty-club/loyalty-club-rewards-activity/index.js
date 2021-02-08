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
    };
  }

  componentDidMount() {
    // Getting user's last reward transaction details.
    // Api doen't require from/to date if we need last transaction details
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
        if (result.data !== undefined && result.data.error === undefined) {
          this.setState({
            activity: result.data.data || null,
          });
        }
        removeInlineLoader('.reward-activity');
      });
    }
  };

  generateStatement = () => {
    const { activity } = this.state;
    if (activity === null || activity === 'undefined') {
      return null;
    }

    const statement = [];

    if (Array.isArray(activity) && activity.length === 0) {
      statement.push(
        <div className="no-reward-activity">{Drupal.t('You have no reward activity to display.')}</div>,
      );
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

  onMenuOpen = (selectRef) => {
    if (selectRef.current === null) {
      return;
    }
    selectRef.current.select.inputRef.closest(`.${selectRef.current.select.props.className}`).classList.add('open');
  };

  onMenuClose = (selectRef) => {
    if (selectRef.current === null) {
      return;
    }
    selectRef.current.select.inputRef.closest(`.${selectRef.current.select.props.className}`).classList.remove('open');
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
    const fromDate = formatDate(date);
    const toDate = formatDate(new Date(date.getFullYear(), date.getMonth() + 1, 0));
    const { type } = this.state;

    this.fetchRewardActivity(fromDate, toDate, 0, type);
    this.setState({
      fromDate,
      toDate,
    });
  };

  render() {
    const { activity, dateFilterOptions } = this.state;
    const transactionTypeOptions = getTransactionTypeOptions();

    return (
      <div className="loyalty-club-rewards-wrapper loyalty-tab-content fadeInUp" style={{ animationDelay: '0.6s' }}>
        <div className="filters">
          <Select
            ref={this.dateSelectRef}
            classNamePrefix="spcAuraSelect"
            className="transaction-date-filter"
            name="transactionDateFilter"
            onMenuOpen={() => this.onMenuOpen(this.dateSelectRef)}
            onMenuClose={() => this.onMenuClose(this.dateSelectRef)}
            options={dateFilterOptions}
            defaultValue={dateFilterOptions[0]}
            value={getTransactionDateOptionsDefaultValue(activity)}
            onChange={this.handleDateChange}
          />
          <Select
            ref={this.typeSelectRef}
            classNamePrefix="spcAuraSelect"
            className="transaction-type-filter"
            name="transactionTypeFilter"
            onMenuOpen={() => this.onMenuOpen(this.typeSelectRef)}
            onMenuClose={() => this.onMenuClose(this.typeSelectRef)}
            options={transactionTypeOptions}
            defaultValue={transactionTypeOptions[0]}
            onChange={this.handleTypeChange}
          />
        </div>
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
