import React from 'react';

const Points = (props) => {
  const { points } = props;

  return (
    <span className="points">{`${points} ${Drupal.t('Pts')}`}</span>
  );
};

export default Points;
