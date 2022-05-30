import React from 'react';
import { getPercentage } from '../../../../utilities';

const TierProgress = ({
  currentTier,
  nextTier,
  memberPointsInfo,
}) => {
  const pointsPercent = getPercentage(memberPointsInfo);

  return (
    <div className="my-tier-progress">
      <div className="progress-label">{currentTier}</div>
      <div className="progress-wrapper">
        <div className="tier-bar-back">
          <ul>
            <li />
            <li />
            <li />
          </ul>
          <div style={{ width: `${pointsPercent}%` }} className="tier-bar-front" />
        </div>
      </div>
      <div className="progress-label">{nextTier}</div>
    </div>
  );
};

export default TierProgress;
