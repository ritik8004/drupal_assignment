import React from 'react';
import PdpPopupContent from '../utilities/pdp-popup-content';

const PpdPanel = (props) => {
  const {
    panelContent,
  } = props;

  const content = [];

  panelContent.forEach(() => {
    content.push(<PdpPopupContent>{panelContent}</PdpPopupContent>);
  });

  return (
    <div className="magv2-popup-panel">
      {content}
    </div>
  );
};

export default PpdPanel;
