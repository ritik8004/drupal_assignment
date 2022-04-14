import React from 'react';
import parse from 'html-react-parser';
import { formatDate } from '../../../../../../alshaya_aura_react/js/utilities/reward_activity_helper';

const PointsExpiryMessage = (props) => {
  const {
    points,
    date,
  } = props;
  const message = `${points} ${Drupal.t('points')} ${Drupal.t('will expire by')} <b>${formatDate(date, 'DD-Mon-YYYY')}</b>`;

  // If 0 points are expiring, do nothing.
  if (points === 0) {
    return null;
  }

  return (
    <div className="spc-aura-points-expiry-item">
      <div>
        {parse(message)}
      </div>
    </div>
  );
};

export default PointsExpiryMessage;
