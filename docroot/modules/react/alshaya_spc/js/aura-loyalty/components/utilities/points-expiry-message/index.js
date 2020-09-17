import React from 'react';

const PointsExpiryMessage = () => {
  // @todo: Assuming we will get this message with bold tags from API.
  const points = 700;
  const date = Drupal.t('30th June');
  const message = `<b>${points} ${Drupal.t('points')}</b> ${Drupal.t('will expire by')} <b>${date}</b>`;

  return (
    <div className="spc-aura-points-expiry-item" dangerouslySetInnerHTML={{ __html: message }} />
  );
};

export default PointsExpiryMessage;
