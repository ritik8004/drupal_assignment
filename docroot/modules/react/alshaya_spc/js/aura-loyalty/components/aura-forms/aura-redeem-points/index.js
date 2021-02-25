import React from 'react';
import AuraRedeemPointsTextField from '../aura-redeem-textfield';
import ConditionalView from '../../../../common/components/conditional-view';
import {
  getPointToPrice,
  showError,
  removeError,
} from '../../../../../../alshaya_aura_react/js/utilities/aura_utils';
import getStringMessage from '../../../../utilities/strings';
import { redeemAuraPoints } from '../../utilities/checkout_helper';
import {
  getUserDetails,
  getPointToPriceRatio,
  getAuraConfig,
} from '../../../../../../alshaya_aura_react/js/utilities/helper';
import { showFullScreenLoader } from '../../../../../../js/utilities/showRemoveFullScreenLoader';
import PriceElement from '../../../../utilities/special-price/PriceElement';
import dispatchCustomEvent from '../../../../utilities/events';

class AuraFormRedeemPoints extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      enableSubmit: false,
      money: null,
      points: null,
      auraTransaction: false,
    };
  }

  componentDidMount() {
    document.addEventListener('auraRedeemPointsApiInvoked', this.handleRedeemPointsEvent, false);
    // Event listener for any change in payment methods section.
    // On payment method update, we recalculate and prefill redemption section.
    document.addEventListener('refreshCompletePurchaseSection', this.updatePointsAndMoney, false);
    // Event listener on delivery information update to remove redeemed points.
    document.addEventListener('refreshCartOnAddress', this.undoRedeemPoints, false);
    // Event listener on CnC store selection.
    document.addEventListener('storeSelected', this.undoRedeemPoints, false);

    const { totals } = this.props;

    // If amount paid with aura is undefined or null, we calculate and
    // refill redemption input elements and return.
    if (totals.paidWithAura === undefined || totals.paidWithAura === null) {
      this.updatePointsAndMoney();
      return;
    }

    this.setState({
      money: totals.paidWithAura,
      points: totals.paidWithAura * getPointToPriceRatio(),
      auraTransaction: true,
    });
    // Add a class for FE purposes.
    document.querySelector('.spc-aura-redeem-points-form-wrapper').classList.add('redeemed');
  }

  // Set points and money in state to prefill redemption input elements.
  updatePointsAndMoney = () => {
    const { totals } = this.props;

    // If amount paid with aura is not present in cart totals, we calculate
    // points and money to refill redemption input elements.
    if (totals.paidWithAura === undefined || totals.paidWithAura === null) {
      const pointsToPrefill = this.redemptionLimit();

      if (pointsToPrefill === 0) {
        return;
      }

      this.setState({
        money: getPointToPrice(pointsToPrefill),
        points: pointsToPrefill,
        enableSubmit: true,
      });
    }
  }

  // Minimum of total points in user account and order total value
  // in points is the redemption limit.
  redemptionLimit = () => {
    const { totals, pointsInAccount } = this.props;
    const { base_grand_total: grandTotal } = totals;
    const grandTotalPoints = grandTotal * getPointToPriceRatio();

    const pointsAllowedToRedeem = (pointsInAccount < grandTotalPoints)
      ? pointsInAccount
      : grandTotalPoints;

    return pointsAllowedToRedeem;
  }

  handleRedeemPointsEvent = (data) => {
    const { stateValues, action } = data.detail;

    if (Object.keys(stateValues).length === 0 || stateValues.error === true) {
      showError('spc-aura-link-api-response-message', drupalSettings.global_error_message);
      // Reset redemption input fields to initial value.
      this.resetInputs();
      return;
    }

    const { totals } = this.props;
    let cartTotals = totals;

    if (action === 'set points') {
      // Add aura details in totals.
      cartTotals = { ...cartTotals, ...stateValues };

      stateValues.auraTransaction = true;
      // Add a class for FE purposes.
      document.querySelector('.spc-aura-redeem-points-form-wrapper').classList.add('redeemed');
    } else if (action === 'remove points') {
      // Reset redemption input fields to initial value.
      this.resetInputs();

      // Remove all aura related keys from totals if present.
      Object.entries(stateValues).forEach(([key]) => {
        delete cartTotals[key];
      });

      // Remove class.
      document.querySelector('.spc-aura-redeem-points-form-wrapper').classList.remove('redeemed');
    }

    this.setState({
      ...stateValues,
    });

    // Dispatch an event to update totals in cart object.
    dispatchCustomEvent('updateTotalsInCart', { totals: cartTotals });
  };

  convertPointsToMoney = (e) => {
    removeError('spc-aura-link-api-response-message');
    // @todo: Run some proper validations, for now just checking length.
    if (e.target.value.length >= 1) {
      this.setState({
        enableSubmit: true,
      });
    } else {
      this.setState({
        enableSubmit: false,
      });
    }

    // Convert to money.
    if (e.target.value > 0) {
      this.setState({
        points: e.target.value,
        money: getPointToPrice(e.target.value),
      });
    } else {
      this.setState({
        points: null,
        money: null,
      });
    }
  };

  redeemPoints = () => {
    removeError('spc-aura-link-api-response-message');
    const { isoCurrencyCode } = getAuraConfig();
    const { points, money } = this.state;
    const { cardNumber } = this.props;

    if (points === null) {
      showError('spc-aura-link-api-response-message', getStringMessage('form_error_empty_points'));
      return;
    }

    const maxPointsToRedeem = this.redemptionLimit();

    if (parseInt(points, 10) > parseInt(maxPointsToRedeem, 10)) {
      showError('spc-aura-link-api-response-message', `${getStringMessage('you_can_redeem_maximum')} ${maxPointsToRedeem} ${getStringMessage('points')}`);
      return;
    }

    // Call API to redeem aura points.
    const data = {
      action: 'set points',
      userId: getUserDetails().id || 0,
      redeemPoints: points,
      moneyValue: money,
      currencyCode: isoCurrencyCode,
      cardNumber,
    };
    showFullScreenLoader();
    redeemAuraPoints(data);
  };

  undoRedeemPoints = () => {
    removeError('spc-aura-link-api-response-message');
    const { cardNumber } = this.props;
    // Call API to undo redeem aura points.
    const data = {
      action: 'remove points',
      userId: getUserDetails().id || 0,
      cardNumber,
    };
    showFullScreenLoader();
    redeemAuraPoints(data);
  }

  // Reset redemption input fields to initial value.
  resetInputs = () => {
    this.setState({
      auraTransaction: false,
      enableSubmit: false,
      points: null,
      money: null,
    });
    // We clear input values from the form elements.
    const input = document.querySelector('.spc-aura-redeem-points-form-wrapper .form-items input.spc-aura-redeem-field-points');
    input.value = '';
  }

  getPointsRedeemedMessage = () => {
    const {
      points,
      money,
      auraTransaction,
    } = this.state;

    if (!auraTransaction || points === null || money === null) {
      return null;
    }

    return [
      <span key="points" className="spc-aura-highlight">{`${points} ${getStringMessage('points')}`}</span>,
      <span key="worth" className="spc-aura-redeem-text">{`${getStringMessage('worth')}`}</span>,
      <span key="money" className="spc-aura-highlight"><PriceElement amount={money} /></span>,
      <span key="redeemed" className="spc-aura-redeem-text">{`${getStringMessage('have_been_redeemed')}`}</span>,
    ];
  }

  render() {
    const {
      enableSubmit,
      money,
      points,
      auraTransaction,
    } = this.state;

    const { currency_code: currencyCode } = drupalSettings.alshaya_spc.currency_config;

    const { totals } = this.props;

    return (
      <div className="spc-aura-redeem-points-form-wrapper">
        <span className="label">{ getStringMessage('checkout_use_your_points') }</span>
        <div className="form-items">
          <div className="inputs">
            <ConditionalView condition={auraTransaction === false}>
              <AuraRedeemPointsTextField
                name="spc-aura-redeem-field-points"
                placeholder="0"
                onChangeCallback={this.convertPointsToMoney}
                value={points}
              />
              <span className="spc-aura-redeem-points-separator">=</span>
              <AuraRedeemPointsTextField
                name="spc-aura-redeem-field-amount"
                placeholder={`${currencyCode} 0.000`}
                money={money}
                currencyCode={currencyCode}
                type="money"
              />
            </ConditionalView>
            <ConditionalView condition={auraTransaction === true}>
              {this.getPointsRedeemedMessage()}
            </ConditionalView>
          </div>
          <ConditionalView condition={auraTransaction === false}>
            <button
              type="submit"
              className="spc-aura-redeem-form-submit spc-aura-button"
              onClick={() => this.redeemPoints()}
              disabled={!enableSubmit}
            >
              { getStringMessage('checkout_use_points') }
            </button>
          </ConditionalView>
          <ConditionalView condition={auraTransaction === true}>
            <button
              type="submit"
              className="spc-aura-redeem-form-submit spc-aura-button"
              onClick={() => this.undoRedeemPoints()}
            >
              { getStringMessage('remove') }
            </button>
          </ConditionalView>
        </div>
        <div id="spc-aura-link-api-response-message" className="spc-aura-link-api-response-message" />
        {totals.balancePayable <= 0
          && <span id="payment-method-aura_payment" />}
      </div>
    );
  }
}

export default AuraFormRedeemPoints;
