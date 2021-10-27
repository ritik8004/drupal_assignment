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
            {Drupal.t('Start spending to earn points')}
          </span>
        </div>
      </>
    );
  }

  if (showDotClass === 'pointer') {
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

  return (
    <>
      <div className="aura-progress-string">
        <span className="aura-progress-string--string">
          {`${Drupal.t('Earn more')} ${difference} ${Drupal.t('points to reach')} ${nextTierLabel} ${Drupal.t('status')}`}
        </span>
      </div>
    </>
  );
};

export default AuraProgressString;
