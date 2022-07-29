import React from 'react';
import parse from 'html-react-parser';
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
    const apiData = window.auraBackend.getAuraPointsToEarn(items, cardNumber);

    if (apiData instanceof Promise) {
      apiData.then((result) => {
        removeFullScreenLoader();
        if (result.data !== undefined && result.data.error === undefined) {
          if (result.data.status) {
            this.setState({
              auraPointsToEarn: result.data.data.apc_points,
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
        {parse(getStringMessage('aura_points_earn_message', {
          '@points': auraPointsToEarn,
        }))}
      </div>
    );
  }
}

export default AuraPointsToEarn;
