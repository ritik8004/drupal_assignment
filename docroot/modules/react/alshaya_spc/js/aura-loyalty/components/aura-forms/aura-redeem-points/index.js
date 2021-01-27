import React from 'react';
import AuraRedeemPointsTextField from '../aura-redeem-textfield';
import ConditionalView from '../../../../common/components/conditional-view';
import {
  getPointToPrice,
  getPriceToPoint,
  showError,
  removeError,
} from '../../../../../../alshaya_aura_react/js/utilities/aura_utils';
import getStringMessage from '../../../../utilities/strings';
import { redeemAuraPoints } from '../../utilities/checkout_helper';
import { getUserDetails } from '../../../../../../alshaya_aura_react/js/utilities/helper';
import { showFullScreenLoader } from '../../../../../../js/utilities/showRemoveFullScreenLoader';
import PriceElement from '../../../../utilities/special-price/PriceElement';

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

    const { totals } = this.props;

    if (totals.paidWithAura === undefined || totals.paidWithAura === null) {
      return;
    }

    this.setState({
      money: totals.paidWithAura,
      points: getPriceToPoint(totals.paidWithAura),
      auraTransaction: true,
    });
    // Add a class for FE purposes.
    document.querySelector('.spc-aura-redeem-points-form-wrapper').classList.add('redeemed');
  }

  handleRedeemPointsEvent = (data) => {
    const { stateValues, action } = data.detail;

    if (Object.keys(stateValues).length === 0 || stateValues.error === true) {
      showError('spc-aura-link-api-response-message', drupalSettings.global_error_message);
      // Reset redemption input fields to initial value.
      this.resetInputs();
      return;
    }

    if (action === 'set points') {
      stateValues.auraTransaction = true;
      // Add a class for FE purposes.
      document.querySelector('.spc-aura-redeem-points-form-wrapper').classList.add('redeemed');
    } else if (action === 'remove points') {
      // Reset redemption input fields to initial value.
      this.resetInputs();
      // Remove class.
      document.querySelector('.spc-aura-redeem-points-form-wrapper').classList.remove('redeemed');
    }

    this.setState({
      ...stateValues,
    });
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
    const { currency_code: currencyCode } = drupalSettings.alshaya_spc.currency_config;
    const { points, money } = this.state;
    const { pointsInAccount, cardNumber } = this.props;

    if (points === null) {
      showError('spc-aura-link-api-response-message', getStringMessage('form_error_empty_points'));
      return;
    }

    if (parseInt(points, 10) > parseInt(pointsInAccount, 10)) {
      showError('spc-aura-link-api-response-message', `${Drupal.t('You can redeem maximum')} ${pointsInAccount} ${Drupal.t('points')}`);
      return;
    }

    // Call API to redeem aura points.
    const data = {
      action: 'set points',
      userId: getUserDetails().id || 0,
      redeemPoints: points,
      moneyValue: money,
      currencyCode,
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
      <span key="points" className="spc-aura-highlight">{`${points} ${Drupal.t('points')}`}</span>,
      <span key="worth" className="spc-aura-redeem-text">{`${Drupal.t('worth')}`}</span>,
      <span key="money" className="spc-aura-highlight"><PriceElement amount={money} /></span>,
      <span key="redeemed" className="spc-aura-redeem-text">{`${Drupal.t('have been successfully redeemed')}`}</span>,
    ];
  }

  render() {
    const {
      enableSubmit,
      money,
      auraTransaction,
    } = this.state;

    const { currency_code: currencyCode } = drupalSettings.alshaya_spc.currency_config;

    return (
      <div className="spc-aura-redeem-points-form-wrapper">
        <span className="label">{ Drupal.t('Use your points') }</span>
        <div className="form-items">
          <div className="inputs">
            <ConditionalView condition={auraTransaction === false}>
              <AuraRedeemPointsTextField
                name="spc-aura-redeem-field-points"
                placeholder="0"
                onChangeCallback={this.convertPointsToMoney}
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
              { Drupal.t('Use points') }
            </button>
          </ConditionalView>
          <ConditionalView condition={auraTransaction === true}>
            <button
              type="submit"
              className="spc-aura-redeem-form-submit spc-aura-button"
              onClick={() => this.undoRedeemPoints()}
            >
              { Drupal.t('Remove') }
            </button>
          </ConditionalView>
        </div>
        <div id="spc-aura-link-api-response-message" className="spc-aura-link-api-response-message" />
      </div>
    );
  }
}

export default AuraFormRedeemPoints;
