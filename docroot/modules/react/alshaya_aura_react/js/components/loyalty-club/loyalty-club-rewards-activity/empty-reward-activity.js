import React from 'react';

const EmptyRewardActivity = () => (
  <>
    <div className="empty-reward-activity-content">
      <span>{`${Drupal.t('You have no previous Aura transactions with ')} `}</span>
      <span className="highlight">{`${Drupal.t('American Eagle')}`}</span>
      <span>{` ${Drupal.t('to display.')}`}</span>
    </div>
    <div>
      {`${Drupal.t('To view rewards activity across all our brands, visit our ')} `}
      <a href="#">{`${Drupal.t('Loyalty app')}`}</a>
    </div>
  </>
);

export default EmptyRewardActivity;
