import React from 'react';

const MemberProgress = () => (
  <div className="my-tier-progress">
    <div className="progress-label">Hello</div>
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
    <div className="progress-label">Plus</div>
  </div>
);

export default MemberProgress;
