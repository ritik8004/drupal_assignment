import React from 'react';
import parse from 'html-react-parser';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';

const LoyaltyPageContent = (props) => {
  const { htmlContent } = props;

  return (
    hasValue(htmlContent) ? (
      <div className="aura-static-content-wrapper">
        {parse(htmlContent)}
      </div>
    ) : ''
  );
};

export default LoyaltyPageContent;
