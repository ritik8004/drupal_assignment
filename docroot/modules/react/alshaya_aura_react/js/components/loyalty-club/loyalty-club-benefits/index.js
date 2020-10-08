import React from 'react';
import parse from 'html-react-parser';
import { getLoyaltyBenefitsContent } from '../../../utilities/helper';

const LoyaltyClubBenefits = () => (
  <>
    {parse(getLoyaltyBenefitsContent())}
  </>
);

export default LoyaltyClubBenefits;
