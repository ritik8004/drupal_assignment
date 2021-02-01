import React from 'react';

const InlineRating = ({
  ReviewsData,
}) => {
  if (ReviewsData !== undefined) {
    return (
      <div className="inline-rating">
        { Object.keys(ReviewsData).map((item) => (
          <div className="aggregate-rating" key={item} itemProp="aggregateRating" itemScope="" itemType="">
            <div className="empty-stars">
              <span style={{ width: `${((parseFloat(ReviewsData[item].ReviewStatistics.AverageOverallRating).toFixed(1)) * 100) / 5}%` }} className="aggregate-star-rating" />
            </div>
            <span className="no-of-stars" itemProp={`${ReviewsData[item].ReviewStatistics.AverageOverallRating}`}>
              {parseFloat(ReviewsData[item].ReviewStatistics.AverageOverallRating).toFixed(1)}
              {' '}
              {Drupal.t('stars')}
            </span>
            <span classNameitemProp={`${ReviewsData[item].ReviewStatistics.TotalReviewCount}`}>
              (
              {ReviewsData[item].ReviewStatistics.TotalReviewCount}
              {' '}
              {Drupal.t('reviews')}
              )
            </span>
          </div>
        ))}
      </div>
    );
  }
  return (
    <div className="inline-rating">
      <div className="aggregate-rating">
        <a className="write-review" href="#">{Drupal.t('Write a Review')}</a>
      </div>
    </div>
  );
};

export default InlineRating;
