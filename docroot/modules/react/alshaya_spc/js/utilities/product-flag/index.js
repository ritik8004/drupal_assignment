import React from 'react';
import ToolTip from '../tooltip';

const ProductFlag = ({
  flag, flagText, tooltipContent, tooltip,
}) => {
  if (flag === '1' && flagText !== undefined && tooltipContent) {
    return (
      <div className="product-flag">
        <ToolTip enable={tooltip}>{tooltipContent}</ToolTip>
        <span className="flag-text fadeInUp" style={{ animationDelay: '0.4s' }}>{flagText}</span>
      </div>
    );
  }
  return (null);
};

export default ProductFlag;
