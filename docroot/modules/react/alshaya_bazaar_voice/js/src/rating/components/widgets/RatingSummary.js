import React from 'react';
import getStringMessage from '../../../../../../js/utilities/strings';
import { getPercentVal } from '../../../utilities/validate';

const RatingSummary = ({
  histogramData,
  totalReviewCount,
}) => {
  histogramData.sort((a, b) => b.RatingValue - a.RatingValue);
  return (
    <div>
      {histogramData.map((value, index) => (
        <div className="histogram-row" key={value.RatingValue}>
          <span className="star-label">
            {value.RatingValue}
            {' '}
            {getStringMessage('star')}
          </span>
          <div className="histogram-full-bar">
            <div style={{ width: `${getPercentVal(value.Count, totalReviewCount)}%` }} className="histogram-dynamic-bar" />
          </div>
          <span className={`histogram-star-count ${index}`}>{value.Count}</span>
        </div>
      ))}
    </div>
  );
};

export default RatingSummary;
