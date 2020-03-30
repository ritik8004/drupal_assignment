import React from 'react';
import parse from 'html-react-parser';

const ToolTip = ({ children, enable, question }) => {
  const iconClass = question === true ? ' question' : ' info';
  if (enable) {
    return (
      <div className={`tooltip-anchor${iconClass}`}>
        <div className="tooltip-box">
          {parse(children)}
        </div>
      </div>
    );
  }
  return (null);
};

export default ToolTip;
