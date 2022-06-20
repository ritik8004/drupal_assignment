import React from 'react';
import { hasValue } from '../../../../../../../js/utilities/conditionsUtility';
import Loading from '../../../../../../../js/utilities/loading';
import logger from '../../../../../../../js/utilities/logger';
import { getApcTierProgressData } from '../../../../hello_member_api_helper';

const tier1Label = 'hello';
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
      userProgressWidth: '0',
    };
  }

  componentDidMount() {
    const apcCustomerData = getApcTierProgressData();
    if (apcCustomerData instanceof Promise) {
      apcCustomerData.then((response) => {
        let currentTier = null;
        let nextTier = null;
        let pointsSummmary = null;
        let tierWidthData = {
          width: '0',
          count: 0,
        };
        let userProgressWidth = '0';
        if (hasValue(response) && !hasValue(response.error) && hasValue(response.data)) {
          currentTier = response.data.extension_attributes.current_tier;
          nextTier = response.data.extension_attributes.next_tier;
          pointsSummmary = response.data.extension_attributes.points_summary;
          tierWidthData = this.getTierWidthData(response.data);
          userProgressWidth = this.getUserProgressWidth(response.data);
        } else if (hasValue(response.error)) {
          logger.error('Error while trying to get apc customer data. Data: @data.', {
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
    if ((tierData.extension_attributes.current_tier === tier1Label)
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
    // If user is new hello member, we check how far he is to become plus member.
    if (tierData.extension_attributes.current_tier === tier1Label) {
      tierObj = tierData.tier_progress_tracker.find((item) => item.code === plusVoucherCode);
      if (hasValue(tierObj)) {
        return (((tierObj.max_value - tierObj.current_value) / tierObj.max_value) * 100);
      }
    }
    // If user is plus member, we check how far he is to get next voucher.
    if ((tierData.extension_attributes.current_tier === tier2Label)
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

    if (wait) {
      return (
        <div className="tier-summary-wrapper" style={{ animationDelay: '0.4s' }}>
          <Loading />
        </div>
      );
    }

    const tierTrackers = [];
    if (currentTier === tier1Label) {
      for (let index = 0; index < tierWidthData.count; index++) {
        tierTrackers.push(<li key={index} style={{ width: `${tierWidthData.width}%` }} />);
      }
    } else if (currentTier === tier2Label) {
      tierTrackers.push(<li key="tier-1" style={{ width: '0%' }} />);
      tierTrackers.push(<li key="tier-2" style={{ width: '100%' }} />);
    }
    return (
      <>
        <div className="my-tier-progress">
          <div className="progress-label">{currentTier}</div>
          <div className="progress-wrapper">
            <div className="tier-bar-back">
              <ul>
                {tierTrackers}
              </ul>
              <div style={{ width: `${userProgressWidth}%` }} className="tier-bar-front" />
            </div>
          </div>
          <div className="progress-label">{nextTier}</div>
        </div>
        <div className="my-points-details">
          {pointsSummmary}
        </div>
      </>
    );
  }
}

export default TierProgress;
