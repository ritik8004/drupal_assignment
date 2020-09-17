import React from 'react';

const PointsPromoMessage = () => {
  // @todo: Assuming we will get this message with bold tags from API.
  const amount = Drupal.t('KWD 50');
  const time = Drupal.t('10PM');
  const points = 5000;

  const message = `${Drupal.t('Spend')} <b>${amount}</b> ${Drupal.t('before')} ${time} ${Drupal.t('today and earn an additional')} <b>${points} ${Drupal.t('points')}</b>`;

  return (
    <div className="spc-aura-points-promo-item" dangerouslySetInnerHTML={{ __html: message }} />
  );
};

export default PointsPromoMessage;
