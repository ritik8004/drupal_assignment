import React from 'react';
import { getHelloMemberPointsToEarn } from '../../../../../../../alshaya_hello_member/js/src/hello_member_api_helper';
import { hasValue } from '../../../../../../../js/utilities/conditionsUtility';
import { getAuraFormConfig } from '../../../../../../../js/utilities/helloMemberHelper';
import logger from '../../../../../../../js/utilities/logger';
import { showFullScreenLoader, removeFullScreenLoader } from '../../../../../../../js/utilities/showRemoveFullScreenLoader';
import getStringMessage from '../../../../../../../js/utilities/strings';

class AuraPointsToEarn extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      auraPointsToEarn: null,
    };
  }

  componentDidMount() {
    document.addEventListener('onLinkCardSuccessful', this.updateEarnPointsData, false);
    const {
      cart: { cart: { items } },
    } = this.props;
    this.setAuraPointsToEarn(items);
  }

  updateEarnPointsData = (e) => {
    if (e.detail) {
      const {
        cart: { cart: { items } },
      } = this.props;
      const cardNumber = e.detail;
      this.setAuraPointsToEarn(items, cardNumber);
    }
  }

  setAuraPointsToEarn = (items, cardNumber = null) => {
    showFullScreenLoader();
    const auraConfig = getAuraFormConfig();
    if (!hasValue(auraConfig)) {
      return;
    }
    const {
      iso_currency_code: isoCurrencyCode,
    } = auraConfig;
    const apiData = getHelloMemberPointsToEarn(items, cardNumber, isoCurrencyCode, 'aura');
    if (apiData instanceof Promise) {
      apiData.then((result) => {
        removeFullScreenLoader();
        if (result.data !== undefined && result.data.error === undefined) {
          this.setState({
            auraPointsToEarn: result.data.apc_points,
          });
          if (result.data.error) {
            logger.notice('Error while trying to get aura points to earn. Message: @message', {
              '@message': result.data.error_message,
            });
          }
        }
      });
    }
  }

  render() {
    const {
      auraPointsToEarn,
    } = this.state;

    if (auraPointsToEarn === null) {
      return null;
    }

    return (
      <div className="points-earned-message">
        {getStringMessage('aura_points_earn_message', {
          '@points': auraPointsToEarn,
        })}
      </div>
    );
  }
}

export default AuraPointsToEarn;
