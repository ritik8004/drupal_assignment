import React from 'react';
import { getUserAuraTier, getUserDetails, getAllAuraTier } from '../../utilities/helper';
import AuraProgressString from './progress-string';
import isRTL from '../../../../alshaya_spc/js/utilities/rtl';
import { getAPIData } from '../../utilities/api/fetchApiData';
import PointsExpiryMessage
  from '../../../../alshaya_spc/js/aura-loyalty/components/utilities/points-expiry-message';
import AuraProgressBar from './progress-bar';
import Loading from '../../../../alshaya_spc/js/utilities/loading';

class AuraProgressWrapper extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      wait: true,
      nextTierLevel: '',
      userPoints: '',
      nextTierThreshold: '',
    };
  }

  componentDidMount() {
    const apiUrl = `get/loyalty-club/get-progress-tracker?uid=${getUserDetails().id}`;
    const apiData = getAPIData(apiUrl);

    if (apiData instanceof Promise) {
      apiData.then((result) => {
        if (result.data !== undefined
          && result.data.error === undefined
          && result.data.data !== undefined
          && result.data.data.length !== 0) {
          this.setState({
            ...result.data.data,
            wait: false,
          });
        }
      });
    }
  }

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
    const {
      wait, nextTierLevel, userPoints, nextTierThreshold,
    } = this.state;

    const {
      expiringPoints,
      expiryDate,
    } = this.props;

    if (wait === true) {
      return (
        <div className="aura-progressbar-wrapper-loading">
          <Loading />
        </div>
      );
    }

    // Current User tier class so we can change gradient for progress bar.
    const currentTierLevel = getUserAuraTier();
    const tierClass = currentTierLevel || 'no-tier';

    // Progress Percentage;
    const progressRatio = (userPoints / nextTierThreshold) * 100;
    const progress = `${progressRatio}%`;

    // Decide if we need to show dot.
    const showDotClass = this.checkDotVisibility(progressRatio);

    return (
      <div className="aura-progressbar-wrapper">
        <AuraProgressBar
          showDotClass={showDotClass}
          tierClass={tierClass}
          currentTierLevel={currentTierLevel}
          nextTierLevel={nextTierLevel}
          progress={progress}
          progressRatio={progressRatio}
          getDotPosition={this.getDotPosition}
        />
        <AuraProgressString
          userPoints={userPoints}
          nextTierThreshold={nextTierThreshold}
          showDotClass={showDotClass}
          nextTierLabel={getAllAuraTier()[nextTierLevel]}
          progressRatio={progressRatio}
        />
        <PointsExpiryMessage
          points={expiringPoints}
          date={expiryDate}
        />
      </div>
    );
  }
}

export default AuraProgressWrapper;
