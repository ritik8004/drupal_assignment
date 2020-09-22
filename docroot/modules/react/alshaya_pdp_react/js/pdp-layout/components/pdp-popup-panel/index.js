import React from 'react';
import PdpPopupContent from '../utilities/pdp-popup-content';

const PpdPanel = (props) => {
  const {
    panelContent,
    skuItemCode,
  } = props;

  const content = [];

  panelContent.forEach((key) => {
    content.push(<PdpPopupContent key={`${key}${skuItemCode}`}>{panelContent}</PdpPopupContent>);
  });

  return (
    <div className="magv2-popup-panel">
      {content}
    </div>
  );
};

export default PpdPanel;
