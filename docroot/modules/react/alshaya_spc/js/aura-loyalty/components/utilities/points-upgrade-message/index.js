import React from 'react';

const PointsUpgradeMessage = (props) => {
  const { msg } = props;


  if (msg.length > 1) {
    return <div className="spc-aura-points-upgrade-item">{msg}</div>;
  }

  return '';
};

export default PointsUpgradeMessage;
