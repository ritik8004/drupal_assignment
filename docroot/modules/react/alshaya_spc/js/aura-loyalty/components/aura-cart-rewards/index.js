import React from 'react';
import ConditionalView from '../../../common/components/conditional-view';
import AuraNotLinkedNoData from './components/not-linked-no-data';
import AuraLinkedVerified from './components/linked-verified';
import AuraLinkedNotVerified from './components/linked-not-verified';
import AuraNotLinkedData from './components/not-linked-data';
import { getAllAuraStatus, getUserDetails } from '../../../../../alshaya_aura_react/js/utilities/helper';
import { getAuraDetailsDefaultState, getAuraLocalStorageKey } from '../../../../../alshaya_aura_react/js/utilities/aura_utils';
import Loading from '../../../utilities/loading';
import getStringMessage from '../../../../../js/utilities/strings';
import { showFullScreenLoader } from '../../../../../js/utilities/showRemoveFullScreenLoader';
import { redeemAuraPoints, getAuraPointsToEarn } from '../utilities/checkout_helper';
import dispatchCustomEvent from '../../../utilities/events';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';

class AuraCartRewards extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      ...getAuraDetailsDefaultState(),
      wait: true,
    };
  }

  componentDidMount() {
    document.addEventListener('loyaltyStatusUpdated', this.updateState, false);
    // Listener to get the updated aura points.
    document.addEventListener('auraPointsToEarnApiInvoked', this.handleAuraPointsToEarn, false);
    // Listener to refreshCart event to track any cart update action like quantity update.
    document.addEventListener('refreshCart', this.getAuraPoints, false);

    if (getUserDetails().id) {
      // Listener to refreshCart event to track any cart update action like quantity update.
      document.addEventListener('refreshCart', this.removeRedeemedPoints, false);
      // Listener to promoCodeSuccess event to track when promo code is applied on cart.
      document.addEventListener('promoCodeSuccess', this.removeRedeemedPoints, false);
      // Listener to track when user clicks on continue to checkout from cart page.
      document.addEventListener('continueToCheckoutFromCart', this.removeRedeemedPoints, false);

      // Listener to redeem points API event to update cart total based on response.
      document.addEventListener('auraRedeemPointsApiInvoked', this.handleRedeemPointsEvent, false);

      // Update state with aura details from prop.
      this.updateAuraDetails();
    } else {
      // Guest user.
      let localStorageValues = Drupal.getItemFromLocalStorage(getAuraLocalStorageKey());

      if (localStorageValues === null) {
        localStorageValues = { wait: false };
      }

      const data = {
        detail: { stateValues: localStorageValues },
      };
      this.updateState(data);
    }
  }

  componentDidUpdate(prevProps) {
    const { auraDetails } = this.props;
    // If there is a change in props value.
    if (prevProps.auraDetails !== auraDetails) {
      // Update state with aura details from prop.
      this.updateAuraDetails();
    }
  }

  // Helper to update state with aura details from prop.
  updateAuraDetails = () => {
    const { auraDetails } = this.props;

    if (hasValue(auraDetails)) {
      const data = {
        detail: { stateValues: auraDetails },
      };
      this.updateState(data);

      const { loyaltyStatus } = this.state;

      if (loyaltyStatus === getAllAuraStatus().APC_NOT_LINKED_NOT_U) {
        this.setState({
          wait: false,
        });
      }
    }
  }

  // Prepare data and call helper to invoke aura points sales API.
  getAuraPoints = (card = '') => {
    const { cardNumber } = this.state;
    const userCardNumber = (typeof card === 'string' && hasValue(card))
      ? card
      : cardNumber;
    this.setState({
      waitForPoints: true,
    });
    getAuraPointsToEarn(userCardNumber);
  }

  // Event listener callback to trigger an event to get aura points.
  handleAuraPointsToEarn = (data) => {
    const states = { ...data.detail.stateValues };
    states.wait = false;
    states.waitForPoints = false;
    this.setState({
      ...states,
    });
  }

  // Event listener callback for redeem points API event to
  // trigger an event to update totals in cart.
  handleRedeemPointsEvent = (data) => {
    const { stateValues, action, cardNumber } = data.detail;

    if (Object.keys(stateValues).length === 0 || stateValues.error) {
      return;
    }

    const { totals } = this.props;
    const cartTotals = totals;

    if (action === 'remove points') {
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
    }
    // Dispatch an event to update totals in cart object.
    dispatchCustomEvent('updateTotalsInCart', { totals: cartTotals });
    // Update aura earn points as per current cart total after appying redemption.
    getAuraPointsToEarn(cardNumber);
  };

  // Event listener callback to remove redeemed points for
  // logged in users on any refreshCart event.
  removeRedeemedPoints = () => {
    const { totals } = this.props;

    // Return if paidWithAura and balancePayable is not present
    // in cart totals that means user has not redeemed any points.
    if (totals.paidWithAura === undefined && totals.balancePayable === undefined) {
      return;
    }

    const { cardNumber } = this.state;

    // Call API to remove redeemed aura points.
    const requestData = {
      action: 'remove points',
      userId: getUserDetails().id,
      cardNumber,
    };
    showFullScreenLoader();
    redeemAuraPoints(requestData);
  };

  updateState = (data) => {
    const states = { ...data.detail.stateValues };
    this.setState({
      ...states,
    });
    // Get the aura points to earn from sales API.
    this.getAuraPoints(states.cardNumber);
  };

  getSectionTitle = (allAuraStatus, loyaltyStatus) => {
    if (loyaltyStatus === allAuraStatus.APC_NOT_LINKED_NO_DATA
      || loyaltyStatus === allAuraStatus.APC_NOT_LINKED_NOT_U) {
      return [
        getStringMessage('cart_page_aura_header'),
        <span key="aura-cart-title">{` ${getStringMessage('checkout_optional')}`}</span>,
      ];
    }
    return getStringMessage('cart_page_aura_header');
  };

  render() {
    const allAuraStatus = getAllAuraStatus();

    const {
      wait,
      expiringPoints,
      expiryDate,
      cardNumber,
      firstName,
      loyaltyStatus,
      auraPointsToEarn,
      waitForPoints,
    } = this.state;

    if (wait) {
      return (
        <div className="spc-aura-cart-rewards-block fadeInUp" style={{ animationDelay: '0.4s' }}>
          <Loading />
        </div>
      );
    }

    return (
      <div className="spc-aura-cart-rewards-block fadeInUp" style={{ animationDelay: '0.4s' }}>

        {/* Guest */}
        <ConditionalView condition={loyaltyStatus === allAuraStatus.APC_NOT_LINKED_NO_DATA
        || loyaltyStatus === allAuraStatus.APC_NOT_LINKED_NOT_U}
        >
          <AuraNotLinkedNoData
            pointsToEarn={auraPointsToEarn}
            loyaltyStatus={loyaltyStatus}
            wait={waitForPoints}
          />
        </ConditionalView>

        {/* Registered with Linked Loyalty Card */}
        <ConditionalView condition={loyaltyStatus === allAuraStatus.APC_LINKED_VERIFIED}>
          <AuraLinkedVerified
            pointsToEarn={auraPointsToEarn}
            expiringPoints={expiringPoints}
            expiryDate={expiryDate}
            loyaltyStatus={loyaltyStatus}
            wait={waitForPoints}
          />
        </ConditionalView>

        {/* Registered with Linked Loyalty Card - Pending Enrollment */}
        <ConditionalView condition={loyaltyStatus === allAuraStatus.APC_LINKED_NOT_VERIFIED}>
          <AuraLinkedNotVerified
            pointsToEarn={auraPointsToEarn}
            loyaltyStatus={loyaltyStatus}
            wait={waitForPoints}
          />
        </ConditionalView>

        {/* Registered with Unlinked Loyalty Card */}
        <ConditionalView condition={loyaltyStatus === allAuraStatus.APC_NOT_LINKED_DATA}>
          <AuraNotLinkedData
            pointsToEarn={auraPointsToEarn}
            cardNumber={cardNumber}
            firstName={firstName}
            wait={waitForPoints}
          />
        </ConditionalView>
      </div>
    );
  }
}

export default AuraCartRewards;
