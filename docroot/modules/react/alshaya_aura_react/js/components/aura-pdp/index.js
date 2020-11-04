import React from 'react';
import { getUserDetails } from '../../utilities/helper';
import { getStorageInfo } from '../../../../js/utilities/storage';
import ToolTip from '../../../../alshaya_spc/js/utilities/tooltip';

class AuraPDP extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      wait: true,
      productPoints: 0,
      cardNumber: '',
    };
  }

  componentDidMount() {
    // @TODO: Check and Move extra code to utility functions.
    // Logged in user.
    if (getUserDetails().id) {
      document.addEventListener('customerDetailsFetched', this.setCardNumber, false);
    } else {
      // Guest user.
      const localStorageValues = getStorageInfo(getAuraLocalStorageKey());
      const data = {
        detail: localStorageValues,
      }
      this.setCardNumber(data);
    }
  }

  setCardNumber = (data) => {
    const { stateValues } = data.detail;
    this.setState({
      cardNumber: stateValues.cardNumber || '',
    });
  };

  getToolTipContent = () => Drupal.t('Everytime you shop you will earn Aura points which can then be redeemed for future purchases. Not eligible for accrual when purchased through Aura points.');

  render() {
    const {
      cardNumber,
      productPoints,
    } = this.state;

    if (!cardNumber) {
      return null;
    }

    return (
      <div className="aura-pdp-points-section">
        <span className="points-text">
          { `${Drupal.t('Earn')} ${productPoints} ${Drupal.t('Aura points')}`}
        </span>
        <ToolTip enable question>{ this.getToolTipContent() }</ToolTip>
      </div>
    );
  }
}

export default AuraPDP;
