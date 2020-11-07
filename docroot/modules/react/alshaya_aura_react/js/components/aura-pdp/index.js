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
      productDetails: [],
    };
  }

  componentDidMount() {
    document.addEventListener('loyaltyStatusUpdatedFromHeader', this.loyaltyStatusUpdated, false);
    document.addEventListener('productPointsFetched', this.updateStates, false);
    document.addEventListener('variantSelectedEvent', this.processVariant, false);
    document.addEventListener('variantQuantityUpdated', this.processVariant, false);

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
    const { productDetails } = this.state;
    this.fetchProductPoints(productDetails);
  };

  updateStates = (data) => {
    const { stateValues } = data.detail;

    this.setState({
      ...stateValues,
    });
  };

  processVariant = (variantDetails) => {
    const { data } = variantDetails.detail;

    if (data.length !== 0) {
      this.setState({
        productDetails: data,
      });
      this.fetchProductPoints(data);
    }
  };

  fetchProductPoints = (productDetails) => {
    const { cardNumber } = this.state;

    if (cardNumber === '' || productDetails.length === 0) {
      this.setState({
        wait: false,
      });
      return;
    }

    // Setting wait as true to show loader while waiting for API response.
    this.setState({
      wait: true,
    });
    getProductPoints(productDetails, cardNumber);
  };

  getToolTipContent = () => Drupal.t('Everytime you shop you will earn Aura points which can then be redeemed for future purchases. Not eligible for accrual when purchased through Aura points.');

  getPointsText = () => {
    const { productPoints } = this.state;

    if (productPoints !== 0) {
      return [
        <span>{`${Drupal.t('Earn')} `}</span>,
        <b>{productPoints}</b>,
        <span>{` ${Drupal.t('Aura points')}`}</span>,
      ];
    }

    return <span>{Drupal.t('Earn Aura points')}</span>;
  };

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
          { this.getPointsText()}
        </span>
        <ToolTip enable question>{ this.getToolTipContent() }</ToolTip>
      </div>
    );
  }
}

export default AuraPDP;
