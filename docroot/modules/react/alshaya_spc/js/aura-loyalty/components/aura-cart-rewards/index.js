import React from 'react';
import SectionTitle from '../../../utilities/section-title';
import ConditionalView from '../../../common/components/conditional-view';
import AuraNotLinkedNoData from './components/not-linked-no-data';
import AuraLinkedVerified from './components/linked-verified';
import AuraLinkedNotVerified from './components/linked-not-verified';
import AuraNotLinkedData from './components/not-linked-data';
import { getAllAuraStatus, getUserDetails } from '../../../../../alshaya_aura_react/js/utilities/helper';
import { getAuraDetailsDefaultState, getAuraLocalStorageKey } from '../../../../../alshaya_aura_react/js/utilities/aura_utils';
import Loading from '../../../utilities/loading';
import { getStorageInfo } from '../../../utilities/storage';
import getStringMessage from '../../../../../js/utilities/strings';
import { showFullScreenLoader } from '../../../../../js/utilities/showRemoveFullScreenLoader';
import { redeemAuraPoints } from '../utilities/checkout_helper';
import dispatchCustomEvent from '../../../utilities/events';

class AuraCartRewards extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      ...getAuraDetailsDefaultState(),
      wait: true,
    };
  }

  componentDidMount() {
    document.addEventListener('loyaltyStatusUpdated', this.updateStates, false);

    if (getUserDetails().id) {
      document.addEventListener('customerDetailsFetched', this.updateStates, false);
      // Listener to refreshCart event to track any cart update action like quantity update.
      document.addEventListener('refreshCart', this.removeRedeemedPoints, false);
      // Listener to promoCodeSuccess event to track when promo code is applied on cart.
      document.addEventListener('promoCodeSuccess', this.removeRedeemedPoints, false);
      // Listener to redeem points API event to update cart total based on response.
      document.addEventListener('auraRedeemPointsApiInvoked', this.handleRedeemPointsEvent, false);

      const { loyaltyStatus } = this.state;

      if (loyaltyStatus === getAllAuraStatus().APC_NOT_LINKED_NOT_U) {
        this.setState({
          wait: false,
        });
      }
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
      this.updateStates(data);
    }
  }

  // Event listener callback for redeem points API event to
  // trigger an event to update totals in cart.
  handleRedeemPointsEvent = (data) => {
    const { stateValues, action } = data.detail;

    if (Object.keys(stateValues).length === 0 || stateValues.error === true) {
      return;
    }

    const { totals } = this.props;
    const cartTotals = totals;

    if (action === 'remove points') {
      // Remove all aura related keys from totals if present.
      Object.entries(stateValues).forEach(([key]) => {
        delete cartTotals[key];
      });
    }
    // Dispatch an event to update totals in cart object.
    dispatchCustomEvent('updateTotalsInCart', { totals: cartTotals });
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

  updateStates = (data) => {
    const states = { ...data.detail.stateValues };
    states.wait = false;
    this.setState({
      ...states,
    });
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
    const { price } = this.props;

    const {
      wait,
      expiringPoints,
      expiryDate,
      cardNumber,
      loyaltyStatus,
    } = this.state;

    if (wait) {
      return (
        <div className="spc-aura-cart-rewards-block fadeInUp" style={{ animationDelay: '0.4s' }}>
          <SectionTitle>{this.getSectionTitle(allAuraStatus, loyaltyStatus)}</SectionTitle>
          <Loading />
        </div>
      );
    }

    return (
      <div className="spc-aura-cart-rewards-block fadeInUp" style={{ animationDelay: '0.4s' }}>
        <SectionTitle>{this.getSectionTitle(allAuraStatus, loyaltyStatus)}</SectionTitle>

        {/* Guest */}
        <ConditionalView condition={loyaltyStatus === allAuraStatus.APC_NOT_LINKED_NO_DATA
        || loyaltyStatus === allAuraStatus.APC_NOT_LINKED_NOT_U}
        >
          <AuraNotLinkedNoData
            price={price}
            loyaltyStatus={loyaltyStatus}
          />
        </ConditionalView>

        {/* Registered with Linked Loyalty Card */}
        <ConditionalView condition={loyaltyStatus === allAuraStatus.APC_LINKED_VERIFIED}>
          <AuraLinkedVerified
            price={price}
            expiringPoints={expiringPoints}
            expiryDate={expiryDate}
            loyaltyStatus={loyaltyStatus}
          />
        </ConditionalView>

        {/* Registered with Linked Loyalty Card - Pending Enrollment */}
        <ConditionalView condition={loyaltyStatus === allAuraStatus.APC_LINKED_NOT_VERIFIED}>
          <AuraLinkedNotVerified
            price={price}
            loyaltyStatus={loyaltyStatus}
          />
        </ConditionalView>

        {/* Registered with Unlinked Loyalty Card */}
        <ConditionalView condition={loyaltyStatus === allAuraStatus.APC_NOT_LINKED_DATA}>
          <AuraNotLinkedData
            price={price}
            cardNumber={cardNumber}
            loyaltyStatus={loyaltyStatus}
          />
        </ConditionalView>
      </div>
    );
  }
}

export default AuraCartRewards;
