import React from 'react';
import { hasValue } from '../../../../../../../js/utilities/conditionsUtility';
import Loading from '../../../../../../../js/utilities/loading';
import logger from '../../../../../../../js/utilities/logger';
import getStringMessage from '../../../../../../../js/utilities/strings';
import { getHelloMemberTierProgressData } from '../../../../hello_member_api_helper';

const tier1Label = 'member';
const tier2Label = 'plus';
const newVoucherCode = 'NEW_COUPON';
const plusVoucherCode = 'GET_PLUS';

class TierProgress extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      wait: true,
      currentTier: null,
      nextTier: null,
      pointsSummmary: null,
      tierWidthData: null,
      userProgressWidth: 0,
    };
  }

  componentDidMount() {
    const helloMembertierData = getHelloMemberTierProgressData();
    if (helloMembertierData instanceof Promise) {
      helloMembertierData.then((response) => {
        let currentTier = null;
        let nextTier = null;
        let pointsSummmary = null;
        let tierWidthData = {
          width: 0,
          count: 0,
        };
        let userProgressWidth = 0;
        if (hasValue(response) && !hasValue(response.error) && hasValue(response.data)) {
          currentTier = response.data.extension_attributes.current_tier;
          nextTier = response.data.extension_attributes.next_tier_en;
          pointsSummmary = response.data.extension_attributes.points_summary;
          tierWidthData = this.getTierWidthData(response.data);
          userProgressWidth = this.getUserProgressWidth(response.data);
        } else if (hasValue(response.error)) {
          logger.error('Error while trying to get hello member tier data. Data: @data.', {
            '@data': JSON.stringify(response),
          });
        }
        this.setState({
          wait: false,
          currentTier,
          nextTier,
          pointsSummmary,
          tierWidthData,
          userProgressWidth,
        });
      });
    }
  }

  /**
   * Get constant tier width value calculated from api response data.
   */
  getTierWidthData = (tierData) => {
    if ((tierData.extension_attributes.current_tier_en === tier1Label)
      && hasValue(tierData.extension_attributes.interval)) {
      const tierObj = tierData.tier_progress_tracker.find((item) => item.code === plusVoucherCode);
      if (hasValue(tierObj)) {
        const tierWidthData = {
          width: (tierData.extension_attributes.interval / tierObj.max_value) * 100,
          count: Math.ceil(tierObj.max_value / tierData.extension_attributes.interval),
        };
        return tierWidthData;
      }
    }
    return null;
  }

  /**
   * Get user progress width value calculated from api response data.
   */
  getUserProgressWidth = (tierData) => {
    let tierObj = null;
    // If no interval data, then user has used all his vouchers.
    // So, we return width as 100%;
    if (hasValue(tierData.extension_attributes.current_tier_en)
      && !hasValue(tierData.extension_attributes.interval)) {
      return 100;
    }
    // If user is new hello member, we check how far he is to become plus member.
    // Here, percentage for tier progress is calculated from max and current value.
    if (tierData.extension_attributes.current_tier_en === tier1Label) {
      tierObj = tierData.tier_progress_tracker.find((item) => item.code === plusVoucherCode);
      if (hasValue(tierObj)) {
        return (((tierObj.max_value - tierObj.current_value) / tierObj.max_value) * 100);
      }
    }
    // If user is plus member, we check how far he is to get next voucher.
    // Here, percentage for tier progress is calculated by total voucher value and current value.
    // User needs certain points to reach the voucher value.
    if ((tierData.extension_attributes.current_tier_en === tier2Label)
      && hasValue(tierData.extension_attributes.interval)) {
      tierObj = tierData.tier_progress_tracker.find((item) => item.code === newVoucherCode);
      if (hasValue(tierObj)) {
        return (((tierData.extension_attributes.interval - tierObj.current_value)
        / tierData.extension_attributes.interval) * 100);
      }
    }
    return null;
  }

  render() {
    const {
      wait,
      currentTier,
      nextTier,
      pointsSummmary,
      tierWidthData,
      userProgressWidth,
    } = this.state;

    const { apcPoints } = this.props;

    if (wait) {
      return (
        <div className="tier-summary-wrapper" style={{ animationDelay: '0.4s' }}>
          <Loading />
        </div>
      );
    }

    if (pointsSummmary === null || currentTier === null) {
      return null;
    }

    const tierTrackers = [];
    // For new hello member, we show n dots calcuated by dividing max value by interval.
    // So, we show n number of dots in the graph and hence n nummber of <li> is required.
    if (currentTier === tier1Label) {
      for (let index = 0; index < tierWidthData.count; index++) {
        tierTrackers.push(<li key={index} style={{ width: `${tierWidthData.width}%` }} />);
      }
    } else if (currentTier === tier2Label) {
    // For plus member, we show only two dots - one at start and other at end
    // It suggests that user has to get certain points to get the next voucher.
      tierTrackers.push(<li key="tier-1" style={{ width: '0%' }} />);
      tierTrackers.push(<li key="tier-2" style={{ width: '100%' }} />);
    }
    return (
      <>
        { currentTier === tier2Label
          && (
          <div className="progress-header">
            {Drupal.t('@plus_label member', { '@plus_label': tier2Label }, { context: 'hello_member' })}
          </div>
          )}
        <div className="my-points">
          <span>{apcPoints}</span>
          <span>{getStringMessage('points_label')}</span>
        </div>
        <div className="my-tier-progress">
          { currentTier !== tier2Label
          && (
            <div className="progress-label">{getStringMessage(currentTier)}</div>
          )}
          <div className="progress-wrapper">
            <div className="tier-bar-back">
              <ul>
                {tierTrackers}
              </ul>
              <div style={{ width: `${userProgressWidth}%` }} className="tier-bar-front" />
            </div>
          </div>
          <div className="progress-label">{getStringMessage(nextTier)}</div>
        </div>
        <div className="my-points-details">
          {pointsSummmary}
        </div>
      </>
    );
  }
}

export default TierProgress;
