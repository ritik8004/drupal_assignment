import React from 'react';
import ToolTip from '../tooltip';
import PriceElement from '../special-price/PriceElement';

const TotalLineItem = (props) => {
  const {
    value,
    name,
    tooltip,
    tooltipContent,
    title,
    showZeroValue,
  } = props;
  if (typeof value === 'string' || value instanceof String) {
    return (
      <div className="total-line-item">
        <span className={name}>
          {title}
          <ToolTip enable={tooltip}>{tooltipContent}</ToolTip>
        </span>
        <span className="value"><span>{value}</span></span>
      </div>
    );
  }

  // If `showZeroValue` is true then we want to display 0 value
  // so skipping this condition which returns null for 0 value.
  if ((showZeroValue === undefined || showZeroValue === false)
    && value === 0) {
    return (null);
  }
  return (
    <div className="total-line-item">
      <span className={name}>
        {title}
        <ToolTip enable={tooltip}>{tooltipContent}</ToolTip>
      </span>
      <span className="value"><PriceElement amount={value} /></span>
    </div>
  );
};

export default TotalLineItem;
