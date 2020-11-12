import React from 'react';
import parse from 'html-react-parser';

const PointsExpiryMessage = (props) => {
  const {
    points,
    date,
  } = props;
  const message = `<b>${points} ${Drupal.t('points')}</b> ${Drupal.t('will expire by')} <b>${date}</b>`;

  // If 0 points are expiring, do nothing.
  if (points === 0) {
    return null;
  }

  return (
    <div className="spc-aura-points-expiry-item">
      {parse(message)}
    </div>
  );
};

export default PointsExpiryMessage;
