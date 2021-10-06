import React from 'react';

const SofaSectionalConfigItem = ({ name, value }) => (
  <div className="sofa-section-config-item-wrapper">
    <div className="sofa-section-config-item-title">
      {name}
    </div>
    <div className="sofa-section-config-item-value">
      {value}
    </div>
  </div>
);

export default SofaSectionalConfigItem;
