import React from 'react';

const PointsToEarnMessage = (props) => {
  const { points, type } = props;

  // @todo: Update condition.
  // Guest User & No card.
  if (type === 'guest-no-card') {
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

  // @todo: Update condition.
  // Registered User & Linked card.
  if (type === 'register-linked-pending' || type === 'register-linked') {
    const toEarnMessage = `${Drupal.t('On completion of this purchase you will earn:')} `;
    const pointsHighlight = `${points} ${Drupal.t('pts')}`;
    return (
      <span className="spc-aura-points-to-earn">
        <span>{ toEarnMessage }</span>
        <span className="spc-aura-highlight">{ pointsHighlight }</span>
      </span>
    );
  }

  // @todo: Update condition.
  // Registered User & UnLinked card.
  if (type === 'register-unlinked') {
    const toEarnMessageP1 = `${Drupal.t('Our members will earn')} `;
    const pointsHighlight = `${points} ${Drupal.t('points')}`;
    const toEarnMessageP2 = ` ${Drupal.t('with this purchase')}`;

    return (
      <span className="spc-aura-points-to-earn">
        { toEarnMessageP1 }
        <span className="spc-aura-highlight">{ pointsHighlight }</span>
        { toEarnMessageP2 }
      </span>
    );
  }

  return (
    <span className="spc-aura-points-to-earn" />
  );
};

export default PointsToEarnMessage;
