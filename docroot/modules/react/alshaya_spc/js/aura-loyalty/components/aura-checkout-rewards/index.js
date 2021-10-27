import React from 'react';
import SectionTitle from '../../../utilities/section-title';
import ConditionalView from '../../../common/components/conditional-view';
import { getAuraDetailsDefaultState, getAuraLocalStorageKey } from '../../../../../alshaya_aura_react/js/utilities/aura_utils';
import { getAllAuraStatus, getUserDetails } from '../../../../../alshaya_aura_react/js/utilities/helper';
import AuraNotLinkedNoDataCheckout from './components/not-linked-no-data-checkout';
import AuraLinkedVerifiedCheckout from './components/linked-verified-checkout';
import AuraLinkedNotVerifiedCheckout from './components/linked-not-verified-checkout';
import AuraNotLinkedDataCheckout from './components/not-linked-data-checkout';
import Loading from '../../../utilities/loading';
import {
  getCustomerDetails,
} from '../../../../../alshaya_aura_react/js/utilities/header_helper';
import { getStorageInfo, removeStorageInfo } from '../../../utilities/storage';
import { isDeliveryTypeSameAsInCart } from '../../../utilities/checkout_util';
import getStringMessage from '../../../../../js/utilities/strings';
import { processCheckoutCart } from '../utilities/checkout_helper';
import {
  showFullScreenLoader,
} from '../../../../../js/utilities/showRemoveFullScreenLoader';
import hasValue from '../../../../../js/utilities/conditionsUtility';

class AuraCheckoutRewards extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      ...getAuraDetailsDefaultState(),
      wait: true,
    };
  }

  componentDidMount() {
    document.addEventListener('loyaltyStatusUpdated', this.updateStates, false);
    const localStorageValues = getStorageInfo(getAuraLocalStorageKey());

    // Logged in user.
    if (getUserDetails().id) {
      // Remove localstorage values if present for a logged in user.
      if (localStorageValues) {
        removeStorageInfo(getAuraLocalStorageKey());
      }

      document.addEventListener('customerDetailsFetched', this.updateStates, false);
      const { loyaltyStatus, tier } = this.state;

      if (loyaltyStatus === getAllAuraStatus().APC_NOT_LINKED_NOT_U) {
        this.setState({
          wait: false,
        });
        return;
      }

      // Get customer details.
      getCustomerDetails(tier, loyaltyStatus);
      return;
    }

    // Guest user.
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

  // Event listener callback to update states.
  updateStates = (data) => {
    const states = { ...data.detail.stateValues };

    // Attach aura card to cart.
    this.attachCardInCart();

    states.wait = false;
    this.setState({
      ...states,
    });
  };

  attachCardInCart = () => {
    const { cart } = this.props;

    // We don't need to attach card for logged in users.
    if (getUserDetails().id) {
      return;
    }

    const localStorageValues = getStorageInfo(getAuraLocalStorageKey());

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

  getSectionTitle = (allAuraStatus, loyaltyStatus) => {
    if (loyaltyStatus === allAuraStatus.APC_NOT_LINKED_NO_DATA
      || loyaltyStatus === allAuraStatus.APC_NOT_LINKED_NOT_U) {
      return [
        getStringMessage('checkout_aura_block_title'),
        <span key="aura-checkout-title">{` ${getStringMessage('checkout_optional')}`}</span>,
      ];
    }
    return getStringMessage('checkout_aura_block_title');
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
    } = this.state;

    const active = this.isActive();
    const activeClass = active ? 'active' : 'in-active';
    // Get price points from total without delivery charges.
    let price = 0;
    const { totals } = cart.cart || {};
    if (typeof totals.base_grand_total_without_surcharge !== 'undefined'
      && typeof totals.shipping_incl_tax !== 'undefined') {
      price = totals.base_grand_total_without_surcharge - totals.shipping_incl_tax;
    }

    if (wait) {
      return (
        <div className={`spc-aura-checkout-rewards-block fadeInUp ${activeClass}`} style={{ animationDelay: animationDelayValue }}>
          <SectionTitle>{ this.getSectionTitle(allAuraStatus, loyaltyStatus) }</SectionTitle>
          <Loading />
        </div>
      );
    }

    return (
      <div className={`spc-aura-checkout-rewards-block fadeInUp ${activeClass}`} style={{ animationDelay: animationDelayValue }}>
        <SectionTitle>{ this.getSectionTitle(allAuraStatus, loyaltyStatus) }</SectionTitle>

        {/* Guest */}
        <ConditionalView condition={loyaltyStatus === allAuraStatus.APC_NOT_LINKED_NO_DATA
        || loyaltyStatus === allAuraStatus.APC_NOT_LINKED_NOT_U}
        >
          <AuraNotLinkedNoDataCheckout price={price} cartId={cart.cart.cart_id || ''} />
        </ConditionalView>

        {/* Registered User - Linked Card */}
        <ConditionalView condition={loyaltyStatus === allAuraStatus.APC_LINKED_VERIFIED}>
          <AuraLinkedVerifiedCheckout
            pointsInAccount={points}
            price={price}
            expiringPoints={expiringPoints}
            expiryDate={expiryDate}
            cardNumber={cardNumber}
            totals={cart.cart.totals}
            paymentMethodInCart={cart.cart.payment.method || ''}
          />
        </ConditionalView>

        {/* Registered User - Linked Card - Pending Enrollment */}
        <ConditionalView condition={loyaltyStatus === allAuraStatus.APC_LINKED_NOT_VERIFIED}>
          <AuraLinkedNotVerifiedCheckout
            pointsInAccount={points}
            price={price}
          />
        </ConditionalView>

        {/* Registered with Unlinked Loyalty Card */}
        <ConditionalView condition={loyaltyStatus === allAuraStatus.APC_NOT_LINKED_DATA}>
          <AuraNotLinkedDataCheckout
            cardNumber={cardNumber}
            price={price}
          />
        </ConditionalView>
      </div>
    );
  }
}

export default AuraCheckoutRewards;
