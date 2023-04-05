import React from 'react';
import parse from 'html-react-parser';
import AuraRedeemPointsTextField from '../aura-redeem-textfield';
import ConditionalView from '../../../../common/components/conditional-view';
import {
  getPointToPrice,
  showError,
  removeError,
} from '../../../../../../alshaya_aura_react/js/utilities/aura_utils';
import getStringMessage from '../../../../utilities/strings';
import { redeemAuraPoints, isUnsupportedPaymentMethod, getAuraPointsToEarn } from '../../utilities/checkout_helper';
import {
  getUserDetails,
  getPointToPriceRatio,
  getAuraConfig,
} from '../../../../../../alshaya_aura_react/js/utilities/helper';
import { showFullScreenLoader } from '../../../../../../js/utilities/showRemoveFullScreenLoader';
import dispatchCustomEvent from '../../../../utilities/events';
import { hasValue } from '../../../../../../js/utilities/conditionsUtility';
import { isEgiftCardEnabled } from '../../../../../../js/utilities/util';

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
    // Event listener on shiping method update to remove redeemed Amount.
    document.addEventListener('changeShippingMethod', this.undoRedeemPoints);

    const { totals } = this.props;

    // If amount paid with aura is undefined or null, we calculate and
    // refill redemption input elements and return.
    if (totals.paidWithAura === undefined || totals.paidWithAura === null) {
      this.updatePointsAndMoney();
      return;
    }

    this.setState({
      money: totals.paidWithAura,
      points: Math.round(totals.paidWithAura * getPointToPriceRatio()),
      auraTransaction: true,
    });
    // Add a class for FE purposes.
    document.querySelector('.spc-aura-redeem-points-form-wrapper').classList.add('redeemed');
  }

  // Set points and money in state to prefill redemption input elements.
  updatePointsAndMoney = () => {
    removeError('spc-aura-link-api-response-message');
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
    const grandTotalPoints = Math.round(grandTotal * getPointToPriceRatio());

    const pointsAllowedToRedeem = (pointsInAccount < grandTotalPoints)
      ? pointsInAccount
      : grandTotalPoints;

    return pointsAllowedToRedeem;
  }

  handleRedeemPointsEvent = (data) => {
    const { stateValues, action, cardNumber } = data.detail;
    const { cart } = this.props;
    let dispatchCheckoutStep3GTM = false;

    if (Object.keys(stateValues).length === 0 || stateValues.error) {
      showError('spc-aura-link-api-response-message', stateValues.message);
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
      // When full payment done by Aura refreshCartOnPaymentMethod event triggers checkout step 3.
      // When partial payment is done by Aura, trigger checkout step 3 from here.
      if (cartTotals.balancePayable > 0) {
        // Update aura partial payment information in static storage.
        Drupal.alshayaSpc.staticStorage.get('cart_raw').totals.total_segments.push({
          code: 'aura_payment',
          title: 'Paid By Aura',
          value: cartTotals.paidWithAura,
        });
        dispatchCheckoutStep3GTM = true;
      }
      // Trigger aura use points datalayer event.
      Drupal.alshayaSeoGtmPushAuraEventData({ action: 'AURA_EVENT_ACTION_USE_POINTS' });
    } else if (action === 'remove points') {
      // Reset redemption input fields to initial value.
      this.resetInputs();
      // When full payment done by Aura refreshCartOnPaymentMethod event triggers checkout step 3.
      // When partial payment is done by Aura, trigger checkout step 3 from here.
      if (cartTotals.balancePayable > 0) {
        // Update aura partial payment information in static storage.
        const rawCart = Drupal.alshayaSpc.staticStorage.get('cart_raw');
        rawCart.totals.total_segments = rawCart.totals.total_segments.filter((item) => item.code !== 'aura_payment');
        Drupal.alshayaSpc.staticStorage.set('cart_raw', rawCart);
        dispatchCheckoutStep3GTM = true;
      }

      // Remove all aura related keys from totals if present.
      Object.entries(stateValues).forEach(([key]) => {
        // Don't remove totalBalancePayable attribute as this will be used in
        // egift to check remaining balance.
        if (key !== 'totalBalancePayable') {
          delete cartTotals[key];
        } else {
          cartTotals.totalBalancePayable = stateValues.totalBalancePayable;
        }
      });

      // Remove class.
      document.querySelector('.spc-aura-redeem-points-form-wrapper').classList.remove('redeemed');
      // Trigger aura remove points datalayer event.
      Drupal.alshayaSeoGtmPushAuraEventData({ action: 'AURA_EVENT_ACTION_REMOVE_POINTS' });
    }

    this.setState({
      ...stateValues,
    });

    // Dispatch an event to update totals in cart object.
    dispatchCustomEvent('updateTotalsInCart', { totals: cartTotals });
    // Dispatch GTM Checkout Step 3 event for partial aura payment.
    if (dispatchCheckoutStep3GTM) {
      dispatchCustomEvent('auraDataReceivedForGtmCheckoutStep3', { cart });
    }
    // Update aura earn points as per current cart total after appying redemption.
    getAuraPointsToEarn(cardNumber);
  };

  convertPointsToMoney = (e) => {
    removeError('spc-aura-link-api-response-message');

    // Disable submit button if no points in input box.
    if (e.target.value.length < 1) {
      this.setState({
        points: null,
        money: null,
        enableSubmit: false,
      });
      return;
    }

    // Convert to money.
    if (e.target.value > 0) {
      this.setState({
        points: parseInt(e.target.value, 10),
        money: getPointToPrice(e.target.value),
        enableSubmit: true,
      });
    }
  };

  redeemPoints = () => {
    removeError('spc-aura-link-api-response-message');
    const { isoCurrencyCode } = getAuraConfig();
    const { points, money } = this.state;
    const {
      cardNumber, totals, pointsInAccount, context,
    } = this.props;

    if (points === null) {
      showError('spc-aura-link-api-response-message', getStringMessage('form_error_empty_points'));
      return;
    }

    const maxPointsToRedeem = this.redemptionLimit();
    const {
      base_grand_total: grandTotal,
      egiftRedeemedAmount,
      totalBalancePayable,
    } = totals;

    const grandTotalPoints = Math.round(grandTotal * getPointToPriceRatio());
    const balancePayablePoints = totalBalancePayable > 0
      ? Math.round(totalBalancePayable * getPointToPriceRatio())
      : null;
    const pointsInInt = parseInt(points, 10);
    let errorMsg = '';

    if (pointsInInt > grandTotalPoints) {
      errorMsg = getStringMessage('points_exceed_order_total');
      // Check if some amount is already redeemed using egift, if YES then
      // check if AURA points are equal to the remaining balance.
    } else if (isEgiftCardEnabled()
      && hasValue(egiftRedeemedAmount)
      && egiftRedeemedAmount > 0
      && ((pointsInInt !== balancePayablePoints))) {
      errorMsg = Drupal.t('You can only redeem full pending balance or use other payment method.');
    } else if (pointsInInt > parseInt(pointsInAccount, 10)) {
      errorMsg = `${getStringMessage('you_can_redeem_maximum')} ${maxPointsToRedeem} ${getStringMessage('points')}`;
    }

    if (errorMsg !== '') {
      showError('spc-aura-link-api-response-message', errorMsg);
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
    redeemAuraPoints(data, context);
  };

  undoRedeemPoints = () => {
    removeError('spc-aura-link-api-response-message');
    const { cardNumber, context } = this.props;
    // Call API to undo redeem aura points.
    const data = {
      action: 'remove points',
      userId: getUserDetails().id || 0,
      cardNumber,
    };
    showFullScreenLoader();
    redeemAuraPoints(data, context);
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
    const { currency_code: currencyCode } = drupalSettings.alshaya_spc.currency_config;
    return (parse(getStringMessage('aura_points_redeemed_successfully_msg', {
      '@points': points,
      '@currencyCode': currencyCode,
      '@money': money,
    })));
  }

  render() {
    const {
      enableSubmit,
      money,
      points,
      auraTransaction,
    } = this.state;

    const { currency_code: currencyCode } = drupalSettings.alshaya_spc.currency_config;
    const { totals, paymentMethodInCart, formActive } = this.props;
    let paymentNotSupported = isUnsupportedPaymentMethod(paymentMethodInCart);

    // Disable Aura payment method if cart contains any virtual product.
    if (!formActive) {
      paymentNotSupported = true;
    }

    return (
      <div className={paymentNotSupported
        ? 'spc-aura-redeem-points-form-wrapper in-active'
        : 'spc-aura-redeem-points-form-wrapper'}
      >
        <span className="label">{ getStringMessage('checkout_check_your_points_value') }</span>
        <div className="form-items">
          <div className="inputs">
            <ConditionalView condition={!auraTransaction}>
              <AuraRedeemPointsTextField
                name="spc-aura-redeem-field-points"
                placeholder="0"
                onChangeCallback={this.convertPointsToMoney}
                value={points}
                disabled={paymentNotSupported}
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
            <ConditionalView condition={auraTransaction}>
              <div className="successful-redeem-msg" data-aura-points-used={points}>
                {this.getPointsRedeemedMessage()}
              </div>
            </ConditionalView>
          </div>
          <ConditionalView condition={!auraTransaction}>
            <button
              type="submit"
              className="spc-aura-redeem-form-submit spc-aura-button"
              onClick={() => this.redeemPoints()}
              disabled={(paymentNotSupported)
                ? true
                : !enableSubmit}
            >
              { getStringMessage('checkout_use_points') }
            </button>
          </ConditionalView>
          <ConditionalView condition={auraTransaction}>
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
        {paymentNotSupported && paymentMethodInCart === 'cashondelivery'
          && <div className="spc-aura-cod-disabled-message">{Drupal.t('Aura points can not be redeemed with cash on delivery.')}</div>}
      </div>
    );
  }
}

export default AuraFormRedeemPoints;
