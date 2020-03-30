import React from 'react';
import parse from 'html-react-parser';

const ToolTip = ({ children, enable, question }) => {
  const iconClass = question === true ? ' question' : ' info';
  if (enable) {
    const toolTip = (typeof children === 'string')
      ? parse(children)
      : children;
    return (
      <div className={`tooltip-anchor${iconClass}`}>
        <div className="tooltip-box">
          {toolTip}
        </div>
      </div>
    );
  }
  return (null);
};

export default ToolTip;
