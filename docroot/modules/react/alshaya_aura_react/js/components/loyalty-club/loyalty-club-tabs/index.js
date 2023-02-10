import React from 'react';
import { getAllAuraStatus } from '../../../utilities/helper';
import LoyaltyClubBenefits from '../loyalty-club-benefits';
import LoyaltyClubRewardsActivity from '../loyalty-club-rewards-activity';

class LoyaltyClubTabs extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      activeTab: 'rewards',
    };
  }

  switchTab = (e) => {
    this.setState({ activeTab: e.target.getAttribute('data-tab-content') });
  };

  render() {
    const { loyaltyStatus } = this.props;
    const loyaltyStatusInt = parseInt(loyaltyStatus, 10);
    const allAuraStatus = getAllAuraStatus();
    const { activeTab } = this.state;
    // The space before active is intentional so as to not add the extra space
    // in the HTML element className.
    const rewardsClass = activeTab === 'rewards' ? ' active' : '';
    const detailsClass = activeTab === 'details' ? ' active' : '';

    // We show tabs only for following conditions:
    // 1. Registered & verified AURA user.
    // 2. Registered & non-verified AURA user.
    if (loyaltyStatusInt === allAuraStatus.APC_LINKED_VERIFIED
      || loyaltyStatusInt === allAuraStatus.APC_LINKED_NOT_VERIFIED) {
      return (
        <>
          <div className="loyalty-club-tabs fadeInUp" style={{ animationDelay: '0.5s' }}>
            <div
              className={`loyalty-tab rewards-activity${rewardsClass}`}
              data-tab-content="rewards"
              onClick={(e) => this.switchTab(e)}
            >
              {Drupal.t('Aura activity')}
            </div>
            <div
              className={`loyalty-tab loyalty-benefits${detailsClass}`}
              data-tab-content="details"
              onClick={(e) => this.switchTab(e)}
            >
              {Drupal.t('Aura benefits')}
            </div>
          </div>
          <div className="loyalty-club-tabs-content">
            <LoyaltyClubRewardsActivity active={rewardsClass} />
            <LoyaltyClubBenefits active={detailsClass} />
          </div>
        </>
      );
    }

    // Empty tabs / No tabs.
    return (
      <div className="loyalty-club-tabs-content">
        <LoyaltyClubBenefits />
      </div>
    );
  }
}

export default LoyaltyClubTabs;
