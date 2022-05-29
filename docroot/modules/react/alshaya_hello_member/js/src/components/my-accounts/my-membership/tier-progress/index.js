import React from 'react';

const TierProgress = ({
  currentTier,
  nextTier,
}) => (
  <div className="my-tier-progress">
    <div className="progress-label">{currentTier}</div>
    <div className="progress-wrapper">
      <div className="tier-bar-back">
        <ul>
          <li />
          <li />
          <li />
        </ul>
        <div style={{ width: '55%' }} className="tier-bar-front" />
      </div>
    </div>
    <div className="progress-label">{nextTier}</div>
  </div>
);

export default TierProgress;
