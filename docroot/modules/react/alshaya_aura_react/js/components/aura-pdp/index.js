import React from 'react';
import { getUserDetails } from '../../utilities/helper';
import { getStorageInfo } from '../../../../js/utilities/storage';
import ToolTip from '../../../../alshaya_spc/js/utilities/tooltip';
import { getAuraLocalStorageKey } from '../../utilities/aura_utils';

class AuraPDP extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      productPoints: 0,
      cardNumber: '',
    };
  }

  componentDidMount() {
    // Logged in user.
    if (getUserDetails().id) {
      document.addEventListener('customerDetailsFetched', this.updateStates, false);
    } else {
      // Guest user.
      const localStorageValues = getStorageInfo(getAuraLocalStorageKey());
      const data = {
        detail: { stateValues: localStorageValues },
      };
      this.updateStates(data);
    }

    document.addEventListener('loyaltyStatusUpdatedFromHeader', this.updateStates, false);
  }

  updateStates = (data) => {
    const { stateValues } = data.detail;
    const states = { ...stateValues };

    this.setState({
      ...states,
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
