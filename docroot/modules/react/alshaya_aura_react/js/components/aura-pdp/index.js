import React from 'react';
import ReactDOM from 'react-dom';
import ToolTip from '../../../../alshaya_spc/js/utilities/tooltip';
import { getPriceToPoint } from '../../utilities/aura_utils';
import { cartAvailableInStorage } from '../../../../alshaya_spc/js/utilities/get_cart';
import { showFullScreenLoader } from '../../../../js/utilities/showRemoveFullScreenLoader';
import { redeemAuraPoints } from '../../../../alshaya_spc/js/aura-loyalty/components/utilities/checkout_helper';
import { getUserDetails } from '../../utilities/helper';

class AuraPDP extends React.Component {
  constructor(props) {
    super(props);
    const { mode } = this.props;

    this.state = {
      productPoints: this.getInitialProductPoints(mode),
      context: mode,
    };
  }

  componentDidMount() {
    document.addEventListener('auraProductUpdate', this.processVariant, false);
    document.addEventListener('auraProductModalOpened', this.loadModalAuraPoints, false);
    document.addEventListener('auraProductModalClosed', this.removeModalAuraPoints, false);
    // Listener to track any update in customer's aura details.
    document.addEventListener('customerDetailsFetched', this.setCustomerDetails, false);
    document.addEventListener('loyaltyStatusUpdated', this.setCustomerDetails, false);
  }

  componentWillUnmount() {
    document.removeEventListener('auraProductUpdate', this.processVariant, false);
  }

  setCustomerDetails = (data) => {
    const { stateValues } = data.detail;
    this.setState({
      ...stateValues,
    });
  };

  getInitialProductPoints = (mode) => {
    let productPoints = 0;

    if (mode === 'main') {
      productPoints = document.querySelector('.content__title_wrapper .price-amount')
        ? parseInt(document.querySelector('.content__title_wrapper .price-amount').innerText, 10)
        : 0;
    } else if (mode === 'related') {
      productPoints = document.querySelector('#drupal-modal .price-amount')
        ? parseInt(document.querySelector('#drupal-modal .price-amount').innerText, 10)
        : 0;
    }

    return productPoints;
  };

  loadModalAuraPoints = () => {
    if (document.querySelector('#aura-pdp-modal')) {
      ReactDOM.render(
        <AuraPDP mode="related" />,
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
        productPoints: data.amount ? getPriceToPoint(data.amount) : 0,
        context,
      });

      // On change in variant or quantity order total amount might change
      // so we remove redeemed aura points.
      this.removeRedeemedPoints();
    }

    return null;
  };

  // Helper to remove redeemed aura points if any.
  removeRedeemedPoints = () => {
    const cart = cartAvailableInStorage();
    const { cardNumber } = this.state;

    // Return if cart not available or paidWithAura and balancePayable is not present
    // in cart totals that means user has not redeemed any points.
    if (cart === false
      || cart === null
      || cart === 'empty'
      || cart.totals.paidWithAura === undefined
      || cart.totals.balancePayable === undefined
      || cardNumber === undefined) {
      return;
    }

    // Call API to remove redeemed aura points.
    const requestData = {
      action: 'remove points',
      userId: getUserDetails().id,
      cardNumber,
    };
    showFullScreenLoader();
    redeemAuraPoints(requestData);
  };

  getToolTipContent = () => Drupal.t('Earn AURA points every time you shop! You can redeem your points to use on future purchases. Not applicable on purchases made using AURA points.');

  getPointsText = () => {
    const { productPoints } = this.state;

    if (productPoints !== 0) {
      return [
        <span>{`${Drupal.t('Earn')} `}</span>,
        <b>{productPoints}</b>,
        <span>{` ${Drupal.t('AURA points')}`}</span>,
      ];
    }

    return <span>{Drupal.t('Earn Aura points')}</span>;
  };

  render() {
    const {
      productPoints,
      context,
    } = this.state;
    const { mode } = this.props;

    if (context !== mode) {
      return null;
    }

    if (productPoints === 0) {
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
