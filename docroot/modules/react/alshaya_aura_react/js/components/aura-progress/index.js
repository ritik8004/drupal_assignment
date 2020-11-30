import React from 'react';
import { getUserAuraTier } from '../../utilities/helper';
import AuraProgressString from './progress-string';
import ConditionalView
  from '../../../../alshaya_spc/js/common/components/conditional-view';
import isRTL from '../../../../alshaya_spc/js/utilities/rtl';

class AuraProgress extends React.Component {
  // Adds the required left/right positioning for the dot based on
  // language direction.
  getDotPosition = (progress) => {
    if (isRTL()) {
      return {
        right: `calc(${progress} - 10px)`,
      };
    }

    return {
      left: `calc(${progress} - 10px)`,
    };
  };

  // Based on progress, decide if we need to show dots. The thresholds are
  // arbitrary for each resolution, based on when the progress bar moves beyond
  // the text.
  checkDotVisibility = (progressRatio) => {
    // If ratio is more than 98, there is no space for dot.
    if (progressRatio < 99) {
      // For tablet and desktop, 15% should move beyond text overlap.
      if (window.innerWidth >= 768 && progressRatio > 15) {
        return 'pointer';
      }
      // For mobile, 25% should move beyond text overlap.
      if (window.innerWidth < 768 && progressRatio > 25) {
        return 'pointer';
      }
    }

    return '';
  };

  render() {
    // Current User tier class so we can change gradient for progress bar.
    const tierLevel = getUserAuraTier();
    // @todo: Need a helper for this, so that we always have machine name
    // Tier labels which are non translated, we are using this as class.
    const nextTierLevel = 'AuraStar';
    const tierClass = tierLevel || 'no-tier';

    // @TODO: Expecting below from state/API or props.
    const currentTierLabel = Drupal.t('Hello');
    const nextTierLabel = Drupal.t('Star');
    const userPoints = 120000;
    const nextTierThreshold = 300000;

    // Progress Percentage;
    const progressRatio = (userPoints / nextTierThreshold) * 100;
    const progress = `${progressRatio}%`;

    // Decide if we need to show dot.
    const showDotClass = this.checkDotVisibility(progressRatio);

    return (
      <div className="aura-progressbar-wrapper">
        <div className={`aura-progress ${showDotClass} fill-${tierClass.replace(/ /g, '')}`}>
          <span className="under">{currentTierLabel}</span>
          <div className="start">
            <div className="fill" style={{ width: progress }}>
              <span className="over">{currentTierLabel}</span>
            </div>
            <ConditionalView condition={showDotClass === 'pointer'}>
              <span
                className="dot"
                style={this.getDotPosition(progress)}
              />
            </ConditionalView>
          </div>
          <div className={`end next-tier-${nextTierLevel}`}><span>{nextTierLabel}</span></div>
        </div>
        <AuraProgressString
          userPoints={userPoints}
          nextTierThreshold={nextTierThreshold}
          showDotClass={showDotClass}
          nextTierLabel={nextTierLabel}
          progressRatio={progressRatio}
        />
      </div>
    );
  }
}

export default AuraProgress;
