import React from 'react';

const SofaSectionalSummaryConfigItem = ({ name, value }) => (
  <div className="sofa-section-config-item-wrapper">
    <div className="sofa-section-config-item-title">
      {`${name}: `}
    </div>
    <div className="sofa-section-config-item-value">
      {value}
    </div>
  </div>
);

export default SofaSectionalSummaryConfigItem;
