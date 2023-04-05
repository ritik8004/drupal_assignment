import React from 'react';
import Points from '../points';

const UserNamePoints = (props) => {
  const { points, tier, firstName } = props;

  return (
    <div className="aura-logged-in-rewards-header">
      <div className="account-name">
        <span className="name">{ firstName }</span>
      </div>
      <div className="aura-logo-points">
        <div className="account-points">
          <Points points={points} tier={tier} />
        </div>
      </div>
    </div>
  );
};

export default UserNamePoints;
