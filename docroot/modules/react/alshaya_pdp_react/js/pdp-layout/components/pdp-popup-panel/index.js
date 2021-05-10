import React from 'react';
import PdpPopupContent from '../utilities/pdp-popup-content';

const PpdPanel = (props) => {
  const {
    panelContent,
  } = props;

  return (
    <div className="magv2-popup-panel">
      <PdpPopupContent>
        {panelContent}
      </PdpPopupContent>
    </div>
  );
};

export default PpdPanel;
