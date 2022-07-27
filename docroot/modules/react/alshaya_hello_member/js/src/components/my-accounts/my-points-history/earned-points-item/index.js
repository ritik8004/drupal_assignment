import React from 'react';
import { hasValue } from '../../../../../../../js/utilities/conditionsUtility';

const EarnedPointsItem = ({
  itemTitle,
  itemPoints,
}) => {
  if (!hasValue(itemTitle) || !hasValue(itemPoints)) {
    return null;
  }

  return (
    <div className="earned-items">
      <div className="item">{itemTitle}</div>
      <div className="points">{itemPoints}</div>
    </div>
  );
};

export default EarnedPointsItem;
