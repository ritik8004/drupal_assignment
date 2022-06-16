import React from 'react';
import { hasValue } from '../../../../../../../js/utilities/conditionsUtility';
import Loading from '../../../../../../../js/utilities/loading';
import { getApcTierProgressData } from '../../../../hello_member_api_helper';

class TierProgress extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      wait: false,
      currentTier: null,
      nextTier: null,
      pointsSummmary: null,
      tierWidthData: null,
      userProgressWidth: null,
    };
  }

  componentDidMount() {
    const apcCustomerData = getApcTierProgressData();
    if (apcCustomerData instanceof Promise) {
      apcCustomerData.then((response) => {
        if (hasValue(response) && hasValue(response.data)) {
          this.setState({
            wait: true,
            currentTier: response.data.extension_attributes.current_tier,
            nextTier: response.data.extension_attributes.next_tier,
            pointsSummmary: response.data.extension_attributes.points_summary,
            tierWidthData: this.getTierWidthData(response.data),
            userProgressWidth: this.getUserProgressWidth(response.data),
          });
        }
      });
    }
  }

  /**
   * Get constant tier width value calculated from api response data.
   */
  getTierWidthData = (tierData) => {
    if ((tierData.extension_attributes.current_tier === 'hello')
      && hasValue(tierData.extension_attributes.interval)) {
      const tierObj = tierData.tier_progress_tracker.find((item) => item.code === 'GET_PLUS');
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
    if (tierData.extension_attributes.current_tier === 'hello') {
      tierObj = tierData.tier_progress_tracker.find((item) => item.code === 'GET_PLUS');
      if (hasValue(tierObj)) {
        return (((tierObj.max_value - tierObj.current_value) / tierObj.max_value) * 100);
      }
    }
    // If user is plus member, we check how far he is to get next voucher.
    if ((tierData.extension_attributes.current_tier === 'plus')
      && hasValue(tierData.extension_attributes.interval)) {
      tierObj = tierData.tier_progress_tracker.find((item) => item.code === 'NEW_COUPON');
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
    const { myMembershipData } = this.props;

    if (!wait || myMembershipData === null) {
      return (
        <div className="tier-summary-wrapper" style={{ animationDelay: '0.4s' }}>
          <Loading />
        </div>
      );
    }

    const tierWidthValue = hasValue(tierWidthData) ? tierWidthData.width : '0';
    const countTiers = hasValue(tierWidthData) ? tierWidthData.count : 1;
    const userProgressWidthValue = hasValue(userProgressWidth) ? userProgressWidth : '0';
    const tierTrackers = [];
    if (currentTier === 'hello') {
      for (let index = 0; index < countTiers; index++) {
        tierTrackers.push(<li key={index} style={{ width: `${tierWidthValue}%` }} />);
      }
    } else if (currentTier === 'plus') {
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
              <div style={{ width: `${userProgressWidthValue}%` }} className="tier-bar-front" />
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
