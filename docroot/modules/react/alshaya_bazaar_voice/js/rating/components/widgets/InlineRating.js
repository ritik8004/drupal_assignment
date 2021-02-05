import React from 'react';
import smoothscroll from 'smoothscroll-polyfill';
import RatingSummary from './RatingSummary';
import DisplayStar from '../stars/DisplayStar';

// Use smoothscroll to fill for Safari and IE,
// Otherwise while scrollIntoView() is supported by all,
// Smooth transition is not supported apart from Chrome & FF.
smoothscroll.polyfill();

const scrollPosition = (e) => {
  e.preventDefault();
  document.querySelector('.review-section').scrollIntoView({
    behavior: 'smooth',
  });
};

const InlineRating = ({
  ReviewsData,
}) => {
  if (ReviewsData !== undefined) {
    return (
      <div className="inline-rating">
        { Object.keys(ReviewsData).map((item) => (
          <div className="aggregate-rating" key={item} itemProp="aggregateRating" itemScope="" itemType="">
            <div className="empty-stars">
              <DisplayStar
                StarPercentage={ReviewsData[item].ReviewStatistics.AverageOverallRating}
              />
              <div className="histogram-data">
                <div className="histogram-title">
                  {ReviewsData[item].ReviewStatistics.TotalReviewCount}
                  {' '}
                  {Drupal.t('reviews')}
                </div>
                <RatingSummary
                  HistogramData={ReviewsData[item].ReviewStatistics.RatingDistribution}
                  TotalReviewCount={ReviewsData[item].ReviewStatistics.TotalReviewCount}
                />
              </div>
            </div>
            <span>
              (
              <a onClick={(e) => scrollPosition(e)} href="#">{ReviewsData[item].ReviewStatistics.TotalReviewCount}</a>
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
        <a onClick={(e) => scrollPosition(e)} className="write-review" href="#">{Drupal.t('Write a Review')}</a>
      </div>
    </div>
  );
};
export default InlineRating;
