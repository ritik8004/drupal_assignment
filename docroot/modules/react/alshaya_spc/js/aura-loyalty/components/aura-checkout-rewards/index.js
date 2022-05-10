import React from 'react';
import ConditionalView from '../../../common/components/conditional-view';
import { getAuraDetailsDefaultState, getAuraLocalStorageKey } from '../../../../../alshaya_aura_react/js/utilities/aura_utils';
import { getAllAuraStatus, getUserDetails } from '../../../../../alshaya_aura_react/js/utilities/helper';
import AuraPointsToEarnedWithPurchase from './components/rewards-points-earned-with-purchase';
import Loading from '../../../utilities/loading';
import {
  getCustomerDetails,
} from '../../../../../alshaya_aura_react/js/utilities/customer_helper';
import { isDeliveryTypeSameAsInCart } from '../../../utilities/checkout_util';
import getStringMessage from '../../../../../js/utilities/strings';
import { getAuraPointsToEarn, processCheckoutCart } from '../utilities/checkout_helper';
import {
  showFullScreenLoader,
} from '../../../../../js/utilities/showRemoveFullScreenLoader';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';
import { isEgiftCardEnabled, isFullPaymentDoneByPseudoPaymentMedthods } from '../../../../../js/utilities/util';
import { cartContainsAnyVirtualProduct } from '../../../utilities/egift_util';
import AuraLinkedCheckout from './components/aura-card';
import { isUserAuthenticated } from '../../../../../js/utilities/helper';


class AuraCheckoutRewards extends React.Component {
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
    let localStorageValues = Drupal.getItemFromLocalStorage(getAuraLocalStorageKey());

    // Logged in user.
    if (getUserDetails().id) {
      // Remove localstorage values if present for a logged in user.
      if (localStorageValues) {
        Drupal.removeItemFromLocalStorage(getAuraLocalStorageKey());
      }

      document.addEventListener('customerDetailsFetched', this.updateState, false);
      const { loyaltyStatus } = this.state;

      if (loyaltyStatus === getAllAuraStatus().APC_NOT_LINKED_NOT_U) {
        this.setState({
          wait: false,
        });
        return;
      }

      // Get customer details.
      getCustomerDetails();
      return;
    }

    // Guest user.
    if (localStorageValues === null) {
      localStorageValues = { wait: false };
    }

    const data = {
      detail: { stateValues: localStorageValues },
    };
    this.updateState(data);
  }

  // Prepare data and call helper to invoke aura points sales API.
  getAuraPoints = () => {
    const {
      cart: { cart: { items } },
    } = this.props;
    const { cardNumber } = this.state;
    this.setState({
      waitForPoints: true,
    });
    getAuraPointsToEarn(items, cardNumber);
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

  // Event listener callback to update states.
  updateState = (data) => {
    const states = { ...data.detail.stateValues };

    // Attach aura card to cart.
    this.attachCardInCart();

    this.setState({
      ...states,
    });

    // Get the aura points to earn from sales API.
    this.getAuraPoints();
  };

  attachCardInCart = () => {
    const { cart } = this.props;

    // We don't need to attach card for logged in users.
    if (getUserDetails().id) {
      return;
    }

    const localStorageValues = Drupal.getItemFromLocalStorage(getAuraLocalStorageKey());

    // In case loyalty card already exists in cart,
    // attach loyalty card only if it's different.
    if (!hasValue(localStorageValues)
      || !hasValue(localStorageValues.cardNumber)
      || localStorageValues.cardNumber === cart.cart.loyaltyCard) {
      return;
    }

    const data = {
      action: 'add',
      type: 'apcNumber',
      value: localStorageValues.cardNumber,
    };

    showFullScreenLoader();
    processCheckoutCart(data);
  };

  getPointsString = (points) => {
    const pointsString = `${points} ${getStringMessage('points')}`;

    return (
      <span className="spc-aura-highlight">{ pointsString }</span>
    );
  };

  isActive = () => {
    const allAuraStatus = getAllAuraStatus();
    const { loyaltyStatus } = this.state;
    const { cart } = this.props;

    // We have redemption available only for linked and verified users so we proceed
    // further to show/hide aura section only for linked and verified user.
    if (loyaltyStatus !== allAuraStatus.APC_LINKED_VERIFIED) {
      return true;
    }

    // If full payment is done by AURA and egift then make sure that AURA is not
    // disabled and other payment methods should get disabled.
    if (isFullPaymentDoneByPseudoPaymentMedthods(cart.cart)) {
      return true;
    }

    // If payment methods is not defined or empty, return false
    // to set aura section as in-active.
    if (cart.cart.payment.methods === undefined || cart.cart.payment.methods.length === 0) {
      return false;
    }

    return isDeliveryTypeSameAsInCart(cart);
  };

  render() {
    const allAuraStatus = getAllAuraStatus();

    const {
      cart,
      animationDelay: animationDelayValue,
    } = this.props;

    const {
      wait,
      points,
      expiringPoints,
      expiryDate,
      loyaltyStatus,
      cardNumber,
      auraPointsToEarn,
      waitForPoints,
    } = this.state;

    const active = this.isActive();
    const activeClass = active ? 'active' : 'in-active';

    // Disable AURA guest user link card form if cart contains virtual products.
    const formActive = !(isEgiftCardEnabled() && cartContainsAnyVirtualProduct(cart.cart));

    if (wait) {
      return (
        <div className={`spc-aura-checkout-rewards-block fadeInUp ${activeClass}`} style={{ animationDelay: animationDelayValue }}>
          <Loading />
        </div>
      );
    }

    return (
      <div className={`spc-aura-checkout-rewards-block fadeInUp ${activeClass}`} style={{ animationDelay: animationDelayValue }}>

        {/* Guest - Show aura section only when card data is available. */}
        <ConditionalView condition={!isUserAuthenticated()
          && (loyaltyStatus === allAuraStatus.APC_LINKED_VERIFIED
          || loyaltyStatus === allAuraStatus.APC_LINKED_NOT_VERIFIED
          || loyaltyStatus === allAuraStatus.APC_NOT_LINKED_DATA)}
        >
          <AuraPointsToEarnedWithPurchase
            pointsToEarn={auraPointsToEarn}
            wait={waitForPoints}
          />
        </ConditionalView>

        {/* Registered User - Linked Card */}
        <ConditionalView condition={isUserAuthenticated()
          && (loyaltyStatus === allAuraStatus.APC_LINKED_VERIFIED
        || loyaltyStatus === allAuraStatus.APC_LINKED_NOT_VERIFIED)}
        >
          <AuraLinkedCheckout
            pointsInAccount={points}
            pointsToEarn={auraPointsToEarn}
            expiringPoints={expiringPoints}
            expiryDate={expiryDate}
            cardNumber={cardNumber}
            totals={cart.cart.totals}
            // Flag to verify if redeem aura points form is accessible or not.
            formActive={formActive}
            paymentMethodInCart={cart.cart.payment.method || ''}
            loyaltyStatus={loyaltyStatus}
            wait={waitForPoints}
            // Flag to verify if Aura payment method is accessible or not.
            methodActive={active}
          />
        </ConditionalView>
      </div>
    );
  }
}

export default AuraCheckoutRewards;
