import React from 'react';

const AuraProgressString = (props) => {
  const {
    userPoints,
    nextTierThreshold,
    showDotClass,
    nextTierLabel,
  } = props;
  const difference = nextTierThreshold - userPoints;

  if (showDotClass === 'pointer') {
    return (
      <div className="aura-progress-string">
        <span className="aura-progress-string--label">{Drupal.t('You are here')}</span>
        <span className="aura-progress-string--string">
          {`${Drupal.t('Earn')} ${difference} ${Drupal.t('more points to reach')} ${nextTierLabel} ${Drupal.t('status')}`}
        </span>
      </div>
    );
  }

  return (
    <div className="aura-progress-string">
      <span className="aura-progress-string--string">
        {`${Drupal.t('Earn')} ${difference} ${Drupal.t('more points to reach')} ${nextTierLabel} ${Drupal.t('status')}`}
      </span>
    </div>
  );
};

export default AuraProgressString;
