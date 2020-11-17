import React from 'react';
import SectionTitle from '../../../utilities/section-title';
import ConditionalView from '../../../common/components/conditional-view';
import { getAuraDetailsDefaultState, getAuraLocalStorageKey } from '../../../../../alshaya_aura_react/js/utilities/aura_utils';
import { getAllAuraStatus, getUserDetails } from '../../../../../alshaya_aura_react/js/utilities/helper';
import AuraNotLinkedNoDataCheckout from './components/not-linked-no-data-checkout';
import AuraLinkedVerifiedCheckout from './components/linked-verified-checkout';
import AuraLinkedNotVerifiedCheckout from './components/linked-not-verified-checkout';

class AuraCheckoutRewards extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      ...getAuraDetailsDefaultState(),
      pointsToEarn: 0,
      pointsInAccount: 0,
      wait: true,
    };
  }

  componentDidMount() {
    // @todo: API call here to fetch the points user will get based on his cart.
    // Alternatively, it might be just a simple sum of points for each product
    // in cart.

    // Event listener to listen to customer data API call event.
    document.addEventListener('customerDetailsFetched', this.updateStates, false);

    // Get customer details.
    getCustomerDetails(tier, loyaltyStatus);
  }

  // Event listener callback to update states.
  updateStates = (data) => {
    const states = { ...data.detail.stateValues };
    states.wait = false;
    this.setState({
      ...states,
    });
  };

  // updateStates = (data) => {
  //   const { stateValues } = data.detail;
  //   const states = { ...stateValues };

  //   if (stateValues.loyaltyStatus === getAllAuraStatus().APC_LINKED_NOT_VERIFIED) {
  //     states.signUpComplete = true;
  //   }

  //   this.setState({
  //     ...states,
  //   });
  // }

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
        Drupal.t('Aura Rewards'),
        <span>{` ${Drupal.t('(Optional)')}`}</span>,
      ];
    }
    return Drupal.t('Aura Rewards');
  };

  render() {
    const allAuraStatus = getAllAuraStatus();

    const {
      animationDelay: animationDelayValue,
    } = this.props;

    const {
      wait,
      pointsToEarn,
      pointsInAccount,
      expiringPoints,
      expiryDate,
      loyaltyStatus,
    } = this.state;

    if (wait) {
      return <Loading />;
    }

    return (
      <div className="spc-aura-checkout-rewards-block fadeInUp" style={{ animationDelay: animationDelayValue }}>
        <SectionTitle>{ this.getSectionTitle(allAuraStatus, loyaltyStatus) }</SectionTitle>

        {/* Guest */}
        <ConditionalView condition={loyaltyStatus === allAuraStatus.APC_NOT_LINKED_NO_DATA
        || loyaltyStatus === allAuraStatus.APC_NOT_LINKED_NOT_U}
        >
          <AuraNotLinkedNoDataCheckout pointsToEarn={pointsToEarn} />
        </ConditionalView>

        {/* Registered User - Linked Card */}
        <ConditionalView condition={loyaltyStatus === allAuraStatus.APC_LINKED_VERIFIED}>
          <AuraLinkedVerifiedCheckout
            pointsInAccount={pointsInAccount}
            pointsToEarn={pointsToEarn}
            expiringPoints={expiringPoints}
            expiryDate={expiryDate}
          />
        </ConditionalView>

        {/* Registered User - Linked Card - Pending Enrollment */}
        <ConditionalView condition={loyaltyStatus === allAuraStatus.APC_LINKED_NOT_VERIFIED}>
          <AuraLinkedNotVerifiedCheckout
            pointsInAccount={pointsInAccount}
            pointsToEarn={pointsToEarn}
          />
        </ConditionalView>
      </div>
    );
  }
}

export default AuraCheckoutRewards;
