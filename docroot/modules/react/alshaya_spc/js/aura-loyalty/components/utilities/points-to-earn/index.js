import React from 'react';

const PointsToEarnMessage = (props) => {
  const { points } = props;
  const { uid } = drupalSettings.user;

  // Guest User & No card.
  if (uid < 1) {
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
  }

  if (uid > 0) {
    // Registered User & Linked card.
    const toEarnMessage = `${Drupal.t('On completion of this purchase you will earn:')} `;
    const pointsHighlight = `${points} ${Drupal.t('pts')}`;
    return (
      <span className="spc-aura-points-to-earn">
        <span>{ toEarnMessage }</span>
        <span className="spc-aura-highlight">{ pointsHighlight }</span>
      </span>
    );
  }

  return (
    <span className="spc-aura-points-to-earn" />
  );
};

export default PointsToEarnMessage;
