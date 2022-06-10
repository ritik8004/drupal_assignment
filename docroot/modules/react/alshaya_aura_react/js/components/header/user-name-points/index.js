import React from 'react';
import AuraHeaderIcon from '../../../svg-component/aura-header-icon';
import {
  getUserDetails,
} from '../../../utilities/helper';
import Points from '../points';

const UserNamePoints = (props) => {
  const { points, tier } = props;
  const { userName } = getUserDetails();

  return (
    <div className="aura-logged-in-rewards-header">
      <div className="account-name">
        <span className="name">{ userName }</span>
      </div>
      <div className="aura-logo-points">
        <div className="aura-logo">
          <AuraHeaderIcon />
        </div>
        <div className="account-points">
          <Points points={points} tier={tier} />
        </div>
      </div>
    </div>
  );
};

export default UserNamePoints;
