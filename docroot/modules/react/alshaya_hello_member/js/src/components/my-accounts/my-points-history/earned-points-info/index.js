import React from 'react';
import { hasValue } from '../../../../../../../js/utilities/conditionsUtility';

const EarnedPointsInfo = ({
  infoTitle,
  infoSubtitle,
}) => {
  if (!hasValue(infoTitle) || !hasValue(infoSubtitle)) {
    return null;
  }

  return (
    <div className="info-items">
      <p className="info-item-title">{infoTitle}</p>
      <p className="info-item-subtitle">{infoSubtitle}</p>
    </div>
  );
};

export default EarnedPointsInfo;
