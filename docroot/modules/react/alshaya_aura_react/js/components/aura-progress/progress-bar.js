import React from 'react';
import { getAllAuraTier } from '../../utilities/helper';
import ConditionalView
  from '../../../../alshaya_spc/js/common/components/conditional-view';

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
    document.getElementById('aura-fill').animate([
      // Keyframes.
      { width: '0' },
      { width: progress },
    ], {
      // Timing options.
      duration: 1250,
      easing: 'ease-in-out',
    });

    setTimeout(() => {
      // Set final position.
      this.setState({
        widthFinalFill: progress,
      });
      // Show the dot.
      document.getElementById('aura-pointer').style.visibility = 'visible';
    }, 1250);
  };

  render() {
    const {
      showDotClass,
      tierClass,
      currentTierLevel,
      nextTierLevel,
      progress,
      getDotPosition,
    } = this.props;

    const { widthFinalFill } = this.state;

    return (
      <div className={`aura-progress ${showDotClass} fill-${tierClass.replace(/ /g, '')}`}>
        <span className="under">{getAllAuraTier()[currentTierLevel]}</span>
        <div className="start">
          <div id="aura-fill" className="fill" style={{ width: widthFinalFill }}>
            <span className="over">{getAllAuraTier()[currentTierLevel]}</span>
          </div>
          <ConditionalView condition={showDotClass === 'pointer'}>
            <span
              className="dot"
              id="aura-pointer"
              style={getDotPosition(progress)}
            />
          </ConditionalView>
        </div>
        <div className={`end next-tier-${nextTierLevel}`}>
          <span>{getAllAuraTier()[nextTierLevel]}</span>
        </div>
      </div>
    );
  }
}

export default AuraProgressBar;
