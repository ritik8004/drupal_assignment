import React from 'react';
import { getAllAuraTier } from '../../utilities/helper';

class AuraProgressBar extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      widthFinalFill: 0,
    };
  }

  componentDidMount() {
    const {
      progress,
    } = this.props;

    // Animate the width.
    this.animateFill(progress);
  }

  animateFill = (progress) => {
    // Variable for animation timing.
    const duration = 1250;

    document.getElementById('aura-fill').animate([
      // Keyframes.
      { width: '0' },
      { width: progress },
    ], {
      // Timing options.
      duration,
      easing: 'ease-in-out',
    });

    setTimeout(() => {
      // Set final position.
      this.setState({
        widthFinalFill: progress,
      });
      // Show the dot.
      const dot = document.getElementById('aura-pointer');
      if (dot !== null) {
        dot.style.visibility = 'visible';
      }
    }, duration);
  };

  render() {
    const {
      showDotClass,
      tierClass,
      currentTierLevel,
      nextTierLevel,
    } = this.props;

    const { widthFinalFill } = this.state;

    return (
      <>
        <div className="aura-tier-progress-string">{Drupal.t('Your Tier Progress', {}, { context: 'aura' })}</div>
        <div className="aura-progress-bar">
          <div className="under">{getAllAuraTier('value')[currentTierLevel]}</div>
          <div className={`aura-progress ${showDotClass} fill-${tierClass.replace(/ /g, '')}`}>
            <div className="start">
              <div id="aura-fill" className="fill" style={{ width: widthFinalFill }} />
            </div>
          </div>
          <div className={`end next-tier-${nextTierLevel}`}>
            <span>{getAllAuraTier('value')[nextTierLevel]}</span>
          </div>
        </div>
      </>
    );
  }
}

export default AuraProgressBar;
