import React from 'react';

const PointsToEarnMessage = (props) => {
  const { points } = props;
  const toEarnMessageP1 = `${Drupal.t('Earn')} `;
  const pointsHighlight = `${points} ${Drupal.t('Aura')}`;
  const toEarnMessageP2 = ` ${Drupal.t('rewards points with this purchase')}`;

  return (
    <span className="spc-aura-points-to-earn">
      { toEarnMessageP1 }
      <span className="spc-aura-highlight">{ pointsHighlight }</span>
      { toEarnMessageP2 }
    </span>
  );
};

export default PointsToEarnMessage;
