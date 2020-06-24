import React from 'react';
import ToolTip from '../tooltip';

const ProductFlag = (props) => {
  const {
    flag,
    flagText,
    tooltipContent,
    tooltip,
  } = props;
  if (flag === '1') {
    if (flagText !== undefined) {
      return (
        <div className="product-flag">
          <ToolTip enable={tooltip}>{tooltipContent}</ToolTip>
          <span className="flag-text fadeInUp" style={{ animationDelay: '0.4s' }}>{flagText}</span>
        </div>
      );
    }
  }
  return (null);
};

export default ProductFlag;
