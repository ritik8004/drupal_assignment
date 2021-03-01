import React from 'react';
import { getAllAuraTier } from '../../utilities/helper';
import ConditionalView
  from '../../../../alshaya_spc/js/common/components/conditional-view';

const AuraProgressBar = (props) => {
  const {
    showDotClass,
    tierClass,
    currentTierLevel,
    nextTierLevel,
    progress,
    getDotPosition,
  } = props;

  return (
    <div className={`aura-progress ${showDotClass} fill-${tierClass.replace(/ /g, '')}`}>
      <span className="under">{getAllAuraTier()[currentTierLevel]}</span>
      <div className="start">
        <div className="fill" style={{ width: progress }}>
          <span className="over">{getAllAuraTier()[currentTierLevel]}</span>
        </div>
        <ConditionalView condition={showDotClass === 'pointer'}>
          <span
            className="dot"
            style={getDotPosition(progress)}
          />
        </ConditionalView>
      </div>
      <div className={`end next-tier-${nextTierLevel}`}><span>{getAllAuraTier()[nextTierLevel]}</span></div>
    </div>
  );
};

export default AuraProgressBar;
