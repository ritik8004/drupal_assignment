import React from 'react';
import Select from 'react-select';
import { getAPIData } from '../../../utilities/api/fetchApiData';
import { getUserDetails } from '../../../utilities/helper';
import {
  addInlineLoader,
  removeInlineLoader,
} from '../../../utilities/aura_utils';
import {
  getTransactionTypeOptions,
  getTransactionDateOptions,
  formatDate,
} from '../../../utilities/reward_activity_helper';

class LoyaltyClubRewardsActivity extends React.Component {
  constructor(props) {
    super(props);
    this.typeSelectRef = React.createRef();
    this.dateSelectRef = React.createRef();
    this.state = {
      activity: null,
      dateFilterOptions: [],
      fromDate: '',
      toDate: '',
      type: '',
    };
  }

  componentDidMount() {
    this.fetchRewardActivity();
  }

  fetchRewardActivity = (fromDate = '', toDate = '', channel = '') => {
    addInlineLoader('.reward-activity');
    // API call to get reward activity for logged in users.
    const apiUrl = `get/loyalty-club/get-reward-activity?uid=${getUserDetails().id}&fromDate=${fromDate}&toDate=${toDate}&channel=${channel}`;
    const apiData = getAPIData(apiUrl);

    if (apiData instanceof Promise) {
      apiData.then((result) => {
        if (result.data !== undefined && result.data.error === undefined) {
          const { dateFilterOptions } = this.state;
          this.setState({
            activity: result.data.data || null,
            dateFilterOptions: dateFilterOptions.length === 0
              ? getTransactionDateOptions(result.data.data)
              : dateFilterOptions,
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
        <div className="no-reward-activity">You have no reward activity to display.</div>,
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
          <span className="amount">{transaction.orderTotal}</span>
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
    const channel = selectedOption.value !== 'all'
      ? selectedOption.value
      : '';

    this.fetchRewardActivity(fromDate, toDate, channel);
    this.setState({
      type: channel,
    });
  };

  handleDateChange = (selectedOption) => {
    const date = new Date(selectedOption.value);
    const fromDate = formatDate(date);
    const toDate = formatDate(new Date(date.getFullYear(), date.getMonth() + 1, 0));
    const { type } = this.state;

    this.fetchRewardActivity(fromDate, toDate, type);
    this.setState({
      fromDate,
      toDate,
    });
  };

  render() {
    const { dateFilterOptions } = this.state;
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
            defaultValue={dateFilterOptions.length !== 0 ? dateFilterOptions[0] : ''}
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
