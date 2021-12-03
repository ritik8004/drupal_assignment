import React from 'react';

const EmptyRewardActivity = () => (
  <>
    <div className="empty-reward-activity-content">
      {Drupal.t('You currently have no Aura linked transactions.')}
    </div>
    <div>
      {`${Drupal.t('To see your offers and rewards, visit your AURA MENA app or')} `}
      <a href="http://aura-mena.com">aura-mena.com</a>
    </div>
  </>
);

export default EmptyRewardActivity;
