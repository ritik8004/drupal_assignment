import React from 'react';
import ReactDOM from 'react-dom';
import { getUserDetails } from '../../utilities/helper';
import { getStorageInfo } from '../../../../js/utilities/storage';
import ToolTip from '../../../../alshaya_spc/js/utilities/tooltip';
import { getAuraLocalStorageKey } from '../../utilities/aura_utils';
import { getProductPoints, isProductBuyable } from '../../utilities/pdp_helper';

class AuraPDP extends React.Component {
  constructor(props) {
    super(props);
    const { mode, cardNumber } = this.props;
    this.state = {
      productPoints: 0,
      cardNumber: cardNumber || '',
      productDetails: [],
      context: mode,
    };
  }

  componentDidMount() {
    document.addEventListener('loyaltyStatusUpdated', this.loyaltyStatusUpdated, false);
    document.addEventListener('productPointsFetched', this.updateStates, false);
    document.addEventListener('auraProductUpdate', this.processVariant, false);
    document.addEventListener('auraProductModalOpened', this.loadModalAuraPoints, false);
    document.addEventListener('auraProductModalClosed', this.removeModalAuraPoints, false);

    // Logged in user.
    if (getUserDetails().id) {
      const { cardNumber } = this.state;
      if (cardNumber === '') {
        document.addEventListener('customerDetailsFetched', this.loyaltyStatusUpdated, false);
      }
    } else {
      // Guest user.
      const localStorageValues = getStorageInfo(getAuraLocalStorageKey());

      if (localStorageValues === null) {
        return;
      }

      const data = {
        detail: { stateValues: localStorageValues },
      };
      this.loyaltyStatusUpdated(data);
    }
  }

  componentWillUnmount() {
    document.removeEventListener('auraProductUpdate', this.processVariant, false);
  }

  loyaltyStatusUpdated = (data) => {
    const states = { ...data.detail.stateValues };
    const stateData = {
      detail: {
        stateValues: { ...states },
      },
    };
    this.updateStates(stateData);
    const { productDetails } = this.state;
    this.fetchProductPoints(productDetails);
  };

  loadModalAuraPoints = () => {
    if (document.querySelector('#aura-pdp-modal')) {
      const { cardNumber } = this.state;
      ReactDOM.render(
        <AuraPDP mode="related" cardNumber={cardNumber} />,
        document.querySelector('#aura-pdp-modal'),
      );
    }
  };

  removeModalAuraPoints = () => {
    if (document.querySelector('#aura-pdp-modal')) {
      ReactDOM.unmountComponentAtNode(document.getElementById('aura-pdp-modal'));
    }
  };

  updateStates = (data) => {
    const { stateValues, context } = data.detail;
    const { mode } = this.props;

    if (context !== undefined && context !== mode) {
      return null;
    }

    this.setState({
      ...stateValues,
    });

    return null;
  };

  processVariant = (variantDetails) => {
    const { data, context } = variantDetails.detail;
    const { mode } = this.props;

    if (context !== undefined && context !== mode) {
      return null;
    }

    if (data.length !== 0) {
      this.setState({
        productDetails: data,
        context,
      });
      this.fetchProductPoints(data, context);
    }

    return null;
  };

  fetchProductPoints = (productDetails, context) => {
    const { cardNumber } = this.state;

    if (cardNumber === '' || productDetails.length === 0) {
      return;
    }
    getProductPoints(productDetails, cardNumber, context);
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
      cardNumber,
      productPoints,
      context,
    } = this.state;
    const { mode } = this.props;

    if (context !== mode) {
      return null;
    }

    if (!isProductBuyable()) {
      return null;
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
