import React from 'react';
import AuraProgressNextTierMessage from './progress-upgrade-message';

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
        <AuraProgressNextTierMessage
          userPoints={userPoints}
          nextTierThreshold={nextTierThreshold}
          nextTierLabel={nextTierLabel}
        />
      </>
    );
  }

  if (showDotClass === 'pointer') {
    return (
      <>
        <div className="aura-progress-string">
          <span className="aura-progress-string--label">{Drupal.t('You are here')}</span>
          <span className="aura-progress-string--string">
            {`${Drupal.t('Earn more')} ${difference} ${Drupal.t('points to reach')} ${nextTierLabel} ${Drupal.t('status')}`}
          </span>
        </div>
        <AuraProgressNextTierMessage
          userPoints={userPoints}
          nextTierThreshold={nextTierThreshold}
          nextTierLabel={nextTierLabel}
        />
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
      <AuraProgressNextTierMessage
        userPoints={userPoints}
        nextTierThreshold={nextTierThreshold}
        nextTierLabel={nextTierLabel}
      />
    </>
  );
};

export default AuraProgressString;
