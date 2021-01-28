import React from 'react';
import { getUserAuraTier, getUserAuraTierLabel } from '../../../utilities/helper';

const Points = (props) => {
  const { points } = props;

  const tierLabel = getUserAuraTierLabel();
  const tierLevel = getUserAuraTier();

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
