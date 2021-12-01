import React from 'react';

const PointsString = (props) => {
  const { points } = props;
  const pointsString = `${points} ${Drupal.t('points')}`;

  return (
    <span className="spc-aura-highlight">{ pointsString }</span>
  );
};

export default PointsString;
