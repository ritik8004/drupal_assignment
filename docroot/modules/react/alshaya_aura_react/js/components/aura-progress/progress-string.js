import React from 'react';

const AuraProgressString = (props) => {
  const {
    userPoints,
    nextTierThreshold,
    showDotClass,
    nextTierLabel,
    progressRatio,
  } = props;

  const difference = nextTierThreshold - userPoints;

  if (progressRatio === 0) {
    return (
      <>
        <div className="aura-progress-string">
          <span className="aura-progress-string--string">
            {Drupal.t('Start spending to earn points', {}, { context: 'aura' })}
          </span>
        </div>
      </>
    );
  }

  // Show string when user is not in vip tier or
  // if progress ratio is less than 100.
  if (showDotClass === 'pointer' || progressRatio !== 100) {
    return (
      <>
        <div className="aura-progress-string">
          <span className="aura-progress-string--label">{Drupal.t('You are here')}</span>
          <span className="aura-progress-string--string">
            {`${Drupal.t('Earn')} ${difference} ${Drupal.t('points to reach')} ${nextTierLabel} ${Drupal.t('status')}`}
          </span>
        </div>
      </>
    );
  }

  // Don't show progress string to user in vip tier.
  return null;
};

export default AuraProgressString;
