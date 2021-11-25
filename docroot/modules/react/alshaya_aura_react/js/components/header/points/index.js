import React from 'react';
import { getAllAuraTier } from '../../../utilities/helper';

const Points = (props) => {
  const { points, tier } = props;

  const tierLabel = getAllAuraTier('value')[tier];
  const tierLevel = tier;

  const tierClass = tierLevel || 'no-tier';

  return (
    <span className={`points ${tierLabel} badge-${tierClass.replace(/ /g, '')}`}>
      {`${points}`}
      <span className="points-post-text">
        {`${Drupal.t('pts')}`}
      </span>
    </span>
  );
};

export default Points;
