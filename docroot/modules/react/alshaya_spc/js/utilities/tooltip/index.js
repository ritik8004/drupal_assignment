import React from 'react';

const ToolTip = ({ children, enable, question }) => {
  const iconClass = question === true ? ' question' : ' info';
  if (enable) {
    return (
      <div className={`tooltip-anchor${iconClass}`}>
        <div
          className="tooltip-box"
          /* eslint-disable react/no-danger */
          dangerouslySetInnerHTML={{ __html: children }}
        />
      </div>
    );
  }
  return (null);
};

export default ToolTip;
