import React from 'react';
import { getUserDetails } from '../../utilities/helper';
import { getStorageInfo } from '../../../../js/utilities/storage';
import ToolTip from '../../../../alshaya_spc/js/utilities/tooltip';
import { getAuraLocalStorageKey } from '../../utilities/aura_utils';
import { getProductPoints, isProductBuyable } from '../../utilities/pdp_helper';
import Loading from '../../../../alshaya_spc/js/utilities/loading';

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
    document.addEventListener('loyaltyStatusUpdatedFromHeader', this.loyaltyStatusUpdated, false);
    document.addEventListener('productPointsFetched', this.updateStates, false);

    // Logged in user.
    if (getUserDetails().id) {
      document.addEventListener('customerDetailsFetched', this.loyaltyStatusUpdated, false);
    } else {
      // Guest user.
      const localStorageValues = getStorageInfo(getAuraLocalStorageKey());

      if (localStorageValues === null) {
        this.setState({
          wait: false,
        });
        return;
      }

      const data = {
        detail: { stateValues: localStorageValues },
      };
      this.loyaltyStatusUpdated(data);
    }
  }

  loyaltyStatusUpdated = (data) => {
    const states = { ...data.detail.stateValues };
    states.wait = true;
    const stateData = {
      detail: {
        stateValues: { ...states },
      },
    };
    this.updateStates(stateData);
    this.fetchProductPoints();
  };

  updateStates = (data) => {
    const { stateValues } = data.detail;

    this.setState({
      ...stateValues,
    });
  };

  fetchProductPoints = () => {
    const { cardNumber } = this.state;
    if (cardNumber) {
      getProductPoints(cardNumber);
    }
  };

  getToolTipContent = () => Drupal.t('Everytime you shop you will earn Aura points which can then be redeemed for future purchases. Not eligible for accrual when purchased through Aura points.');

  render() {
    const {
      wait,
      cardNumber,
      productPoints,
    } = this.state;

    if (!isProductBuyable()) {
      return null;
    }

    if (wait) {
      return <Loading />;
    }

    if (cardNumber === '' || productPoints === 0) {
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
