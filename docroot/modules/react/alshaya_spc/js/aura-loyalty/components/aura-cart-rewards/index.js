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

class AuraCartRewards extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      ...getAuraDetailsDefaultState(),
      productPoints: 0,
      wait: true,
    };
  }

  componentDidMount() {
    // @TODO: Get product points and update in state.
    document.addEventListener('loyaltyStatusUpdated', this.updateStates, false);

    if (getUserDetails().id) {
      document.addEventListener('customerDetailsFetched', this.updateStates, false);
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
      productPoints,
      expiringPoints,
      expiryDate,
      cardNumber,
      loyaltyStatus,
    } = this.state;

    if (wait) {
      return <Loading />;
    }

    return (
      <div className="spc-aura-cart-rewards-block fadeInUp" style={{ animationDelay: animationDelayValue }}>
        <SectionTitle>{this.getSectionTitle(allAuraStatus, loyaltyStatus)}</SectionTitle>

        {/* Guest */}
        <ConditionalView condition={loyaltyStatus === allAuraStatus.APC_NOT_LINKED_NO_DATA
        || loyaltyStatus === allAuraStatus.APC_NOT_LINKED_NOT_U}
        >
          <AuraNotLinkedNoData
            points={productPoints}
            loyaltyStatus={loyaltyStatus}
          />
        </ConditionalView>

        {/* Registered with Linked Loyalty Card */}
        <ConditionalView condition={loyaltyStatus === allAuraStatus.APC_LINKED_VERIFIED}>
          <AuraLinkedVerified
            points={productPoints}
            expiringPoints={expiringPoints}
            expiryDate={expiryDate}
            loyaltyStatus={loyaltyStatus}
          />
        </ConditionalView>

        {/* Registered with Linked Loyalty Card - Pending Enrollment */}
        <ConditionalView condition={loyaltyStatus === allAuraStatus.APC_LINKED_NOT_VERIFIED}>
          <AuraLinkedNotVerified
            points={productPoints}
            loyaltyStatus={loyaltyStatus}
          />
        </ConditionalView>

        {/* Registered with Unlinked Loyalty Card */}
        <ConditionalView condition={loyaltyStatus === allAuraStatus.APC_NOT_LINKED_DATA}>
          <AuraNotLinkedData
            points={productPoints}
            cardNumber={cardNumber}
            loyaltyStatus={loyaltyStatus}
          />
        </ConditionalView>
      </div>
    );
  }
}

export default AuraCartRewards;
