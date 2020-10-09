import React from 'react';
import parse from 'html-react-parser';
import { getLoyaltyBenefitsTitle, getLoyaltyBenefitsContent } from '../../../utilities/helper';

const LoyaltyClubBenefits = () => (
  <div className="loyalty-club-details-wrapper loyalty-tab-content">
    <div className="title">
      {parse(getLoyaltyBenefitsTitle())}
    </div>
    <div className="details">
      {parse(getLoyaltyBenefitsContent())}
    </div>
  </div>
);

export default LoyaltyClubBenefits;
