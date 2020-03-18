import React from 'react';

const ToolTip = ({ children, enable, question }) => {
  const iconClass = question === true ? ' question' : ' info';
  if (enable) {
    return (
      <div className={`tooltip-anchor${iconClass}`}>
        <div className="tooltip-box">
          {children}
        </div>
      </div>
    );
  }
  return (null);
};

export default ToolTip;
