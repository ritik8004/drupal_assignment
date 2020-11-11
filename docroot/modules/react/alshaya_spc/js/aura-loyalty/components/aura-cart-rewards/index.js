import React from 'react';
import SectionTitle from '../../../utilities/section-title';
import ConditionalView from '../../../common/components/conditional-view';
import AuraNotLinkedNoData from './components/not-linked-no-data';
import AuraLinkedVerified from './components/linked-verified';
import AuraLinkedNotVerified from './components/linked-not-verified';
import AuraNotLinkedData from './components/not-linked-data';
import { getAllAuraStatus } from '../../../../../alshaya_aura_react/js/utilities/helper';
import { getAuraDetailsDefaultState } from '../../../../../alshaya_aura_react/js/utilities/aura_utils';

class AuraCartRewards extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      ...getAuraDetailsDefaultState(),
      productPoints: 0,
    };
  }

  componentDidMount() {
    // @TODO: Get product points and update in state.
    document.addEventListener('customerDetailsFetched', this.updateStates, false);
    document.addEventListener('loyaltyStatusUpdated', this.updateStates, false);
  }

  updateStates = (data) => {
    const { stateValues } = data.detail;
    this.setState({
      ...stateValues,
    });
  };

  render() {
    const allAuraStatus = getAllAuraStatus();

    const {
      animationDelay: animationDelayValue,
    } = this.props;

    const {
      productPoints,
      expiringPoints,
      expiryDate,
      cardNumber,
      loyaltyStatus,
    } = this.state;

    const sectionTitle = (loyaltyStatus === allAuraStatus.APC_NOT_LINKED_NO_DATA
      || loyaltyStatus === allAuraStatus.APC_NOT_LINKED_NOT_U)
      ? Drupal.t('Aura Rewards (Optional)')
      : Drupal.t('Aura Rewards');

    return (
      <div className="spc-aura-cart-rewards-block fadeInUp" style={{ animationDelay: animationDelayValue }}>
        <SectionTitle>{sectionTitle}</SectionTitle>

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
