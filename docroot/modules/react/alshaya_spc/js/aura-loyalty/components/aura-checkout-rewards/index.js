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
import { getStorageInfo } from '../../../utilities/storage';
import { isDeliveryTypeSameAsInCart } from '../../../utilities/checkout_util';

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

    // Logged in user.
    if (getUserDetails().id) {
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

  // Event listener callback to update states.
  updateStates = (data) => {
    const states = { ...data.detail.stateValues };
    states.wait = false;
    this.setState({
      ...states,
    });
  };

  getPointsString = (points) => {
    const pointsString = `${points} ${Drupal.t('points')}`;

    return (
      <span className="spc-aura-highlight">{ pointsString }</span>
    );
  };

  getSectionTitle = (allAuraStatus, loyaltyStatus) => {
    if (loyaltyStatus === allAuraStatus.APC_NOT_LINKED_NO_DATA
      || loyaltyStatus === allAuraStatus.APC_NOT_LINKED_NOT_U) {
      return [
        Drupal.t('Aura rewards'),
        <span key="aura-checkout-title">{` ${Drupal.t('(Optional)')}`}</span>,
      ];
    }
    return Drupal.t('Aura rewards');
  };

  isActive = () => {
    const { cart } = this.props;

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
    const price = cart.cart.cart_total || 0;

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
