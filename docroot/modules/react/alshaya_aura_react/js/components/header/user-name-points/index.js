import React from 'react';
import {
  getUserDetails,
} from '../../../utilities/helper';
import Points from '../points';

const UserNamePoints = (props) => {
  const { points } = props;
  const { userName } = getUserDetails();

  return (
    <div className="aura-logged-in-rewards-header">
      <div className="account-name">
        <span className="name">{ userName }</span>
      </div>
      <div className="account-points">
        <Points points={points} />
      </div>
    </div>
  );
};

export default UserNamePoints;
