import React from 'react';

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
            {Drupal.t('star')}
          </span>
          <div className="histogram-full-bar">
            <div style={{ width: `${((value.Count / totalReviewCount).toFixed(1)) * 100}%` }} className="histogram-dynamic-bar" />
          </div>
          <span className={`histogram-star-count ${index}`}>{value.Count}</span>
        </div>
      ))}
    </div>
  );
};

export default RatingSummary;
