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

class AuraCartRewards extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      ...getAuraDetailsDefaultState(),
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
