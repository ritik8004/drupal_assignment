import React from 'react';
import { getLoyaltyBenefitsTitle } from '../../../utilities/helper';
import ToolTip from '../../../../../alshaya_spc/js/utilities/tooltip';

const LoyaltyClubBenefits = ({ active }) => {
  const loyaltyBenefitsTitle = getLoyaltyBenefitsTitle();

  return (
    <div className={`loyalty-club-details-wrapper loyalty-tab-content fadeInUp${active}`} style={{ animationDelay: '0.4s' }}>
      <div className="title">
        <span className="title-1">{loyaltyBenefitsTitle.title1}</span>
        <span className="title-2">{loyaltyBenefitsTitle.title2}</span>
        <ToolTip enable question>{ Drupal.t('As an Aura member, collect points every time you shop to spend on future purchases, and to unlock exclusive rewards.') }</ToolTip>
      </div>
    </div>
  );
};

export default LoyaltyClubBenefits;
