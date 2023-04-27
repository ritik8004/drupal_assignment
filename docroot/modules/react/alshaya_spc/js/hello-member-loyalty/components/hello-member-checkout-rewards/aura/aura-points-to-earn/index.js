import React from 'react';
import parse from 'html-react-parser';
import { showFullScreenLoader, removeFullScreenLoader } from '../../../../../../../js/utilities/showRemoveFullScreenLoader';
import getStringMessage from '../../../../../../../js/utilities/strings';
import logger from '../../../../../../../js/utilities/logger';
import Loading from '../../../../../../../js/utilities/loading';
import ToolTip from '../../../../../utilities/tooltip';

class AuraPointsToEarn extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      auraPointsToEarn: 0,
      wait: true,
    };
  }

  componentDidMount() {
    document.addEventListener('onLinkCardSuccessful', this.updateEarnPointsData, false);
    this.setAuraPointsToEarn();
  }

  updateEarnPointsData = (e) => {
    if (e.detail) {
      const cardNumber = e.detail;
      this.setAuraPointsToEarn(cardNumber);
    }
  }

  setAuraPointsToEarn = (cardNumber = null) => {
    showFullScreenLoader();
    const apiData = window.auraBackend.getAuraPointsToEarn(cardNumber);
    let auraPointsToEarn = 0;
    if (apiData instanceof Promise) {
      apiData.then((result) => {
        if (result.data !== undefined && result.data.error === undefined) {
          if (result.data.status) {
            auraPointsToEarn = result.data.data.apc_points;
          } else {
            logger.error('Error while trying to fetch aura poinnts for @customerId. Message: @message', {
              '@customerId': cardNumber,
              '@message': result.data.error_message || '',
            });
          }
        }
        this.setState({
          auraPointsToEarn,
          wait: false,
        });
        removeFullScreenLoader();
      });
    }
  }

  render() {
    const {
      auraPointsToEarn,
      wait,
    } = this.state;

    if (wait) {
      return (
        <div className="spc-hello-member-earned-message fadeInUp">
          <Loading />
        </div>
      );
    }

    return (
      <>
        <div className="points-earned-message">
          {parse(getStringMessage('aura_points_earn_message', {
            '@points': auraPointsToEarn,
          }))}
        </div>
        <ToolTip enable question>{getStringMessage('hello_member_points_tooltip')}</ToolTip>
      </>
    );
  }
}

export default AuraPointsToEarn;
