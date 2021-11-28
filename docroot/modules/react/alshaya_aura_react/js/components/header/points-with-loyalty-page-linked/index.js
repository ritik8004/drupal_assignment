import React from 'react';
import {
  getUserDetails,
} from '../../../utilities/helper';
import Points from '../points';

const PointsWithLoyaltyPageLinked = (props) => {
  const { points, isHeaderModalOpen, tier } = props;
  const { id: userId } = getUserDetails();
  const previewClass = isHeaderModalOpen === true ? 'open' : '';

  return (
    <div className={`aura-header-link ${previewClass}`}>
      <a
        className="user-points"
        href={Drupal.url(`user/${userId}/loyalty-club`)}
      >
        <Points points={points} tier={tier} />
      </a>
    </div>
  );
};

export default PointsWithLoyaltyPageLinked;
