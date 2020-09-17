import React from 'react';
import ToolTip from '../tooltip';

const ProductFlag = ({
  flag, flagText, tooltipContent, tooltip,
}) => {
  if (flag === '1' && flagText !== undefined) {
    return (
      <div className="product-flag">
        {tooltipContent
          ? (
            <ToolTip enable={tooltip}>{tooltipContent}</ToolTip>
          )
          : <span className="tooltip-anchor" />}
        <span className="flag-text fadeInUp" style={{ animationDelay: '0.4s' }}>{flagText}</span>
      </div>
    );
  }
  return (null);
};

export default ProductFlag;
