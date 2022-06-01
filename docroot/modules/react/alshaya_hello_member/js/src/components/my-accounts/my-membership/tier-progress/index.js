import React from 'react';
import { getPointsData } from '../../../../utilities';

const TierProgress = ({
  currentTier,
  nextTier,
  memberPointsInfo,
}) => {
  const pointsData = getPointsData(currentTier, memberPointsInfo);
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
          <div style={{ width: `${pointsData.pointsGatheredInPercent}%` }} className="tier-bar-front" />
        </div>
      </div>
      <div className="progress-label">{nextTier}</div>
    </div>
  );
};

export default TierProgress;
