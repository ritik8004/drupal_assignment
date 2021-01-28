import React from 'react';
import { getAllAuraStatus } from '../../../utilities/helper';
import LoyaltyClubBenefits from '../loyalty-club-benefits';
import LoyaltyClubRewardsActivity from '../loyalty-club-rewards-activity';

class LoyaltyClubTabs extends React.Component {
  componentDidMount() {
    const { loyaltyStatus } = this.props;
    const loyaltyStatusInt = parseInt(loyaltyStatus, 10);
    const allAuraStatus = getAllAuraStatus();

    // We show tabs only for registered & verified AURA user.
    // @TODO: Update condition when we have rewards activity.
    if (loyaltyStatusInt === allAuraStatus.APC_LINKED_VERIFIED && 0) {
      // We use this to show the active tab's content on page load.
      const activeTab = document.querySelector('.loyalty-club-tabs .loyalty-tab.active');
      // Get the corresponding content.
      const tabContentSelector = `.loyalty-club-tabs-content .${activeTab.getAttribute('data-tab-content')}`;
      document.querySelector(tabContentSelector).classList.add('active');
    }
  }

  switchTab = (e) => {
    const tabContentSelector = `.loyalty-club-tabs-content .${e.target.getAttribute('data-tab-content')}`;
    // Disable all active tabs.
    document.querySelectorAll('.loyalty-club-tabs > .loyalty-tab').forEach((el) => el.classList.remove('active'));
    document.querySelectorAll('.loyalty-club-tabs-content > .loyalty-tab-content').forEach((el) => el.classList.remove('active'));
    // Make active tab and content visible.
    e.target.classList.add('active');
    document.querySelector(tabContentSelector).classList.add('active');
  };

  render() {
    const { loyaltyStatus } = this.props;
    const loyaltyStatusInt = parseInt(loyaltyStatus, 10);
    const allAuraStatus = getAllAuraStatus();

    // We show tabs only for registered & verified AURA user.
    // @TODO: Update condition when we have rewards activity.
    if (loyaltyStatusInt === allAuraStatus.APC_LINKED_VERIFIED && 0) {
      return (
        <>
          <div className="loyalty-club-tabs fadeInUp" style={{ animationDelay: '0.5s' }}>
            <div className="loyalty-tab rewards-activity active" data-tab-content="loyalty-club-rewards-wrapper" onClick={(e) => this.switchTab(e)}>
              {Drupal.t('Rewards activity')}
            </div>
            <div className="loyalty-tab loyalty-benefits" data-tab-content="loyalty-club-details-wrapper" onClick={(e) => this.switchTab(e)}>
              {Drupal.t('Loyalty benefits')}
            </div>
          </div>
          <div className="loyalty-club-tabs-content">
            <LoyaltyClubRewardsActivity />
            <LoyaltyClubBenefits />
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
