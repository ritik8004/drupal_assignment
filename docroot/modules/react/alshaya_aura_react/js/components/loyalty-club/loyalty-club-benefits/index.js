import React from 'react';
import parse from 'html-react-parser';
import { getLoyaltyBenefitsTitle, getLoyaltyBenefitsContent } from '../../../utilities/helper';

const LoyaltyClubBenefits = () => {
  const loyaltyBenefitsTitle = getLoyaltyBenefitsTitle();

  return (
    <div className="loyalty-club-details-wrapper loyalty-tab-content">
      <div className="title">
        <span className="title-1">{loyaltyBenefitsTitle.title1}</span>
        <span className="title-2">{loyaltyBenefitsTitle.title2}</span>
      </div>
      <div className="details">
        {parse(getLoyaltyBenefitsContent())}
      </div>
    </div>
  );
};

export default LoyaltyClubBenefits;
