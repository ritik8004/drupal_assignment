import React from 'react';
import RatingSummary from './RatingSummary';
import DisplayStar from '../stars/DisplayStar';
import smoothScrollTo from '../../../utilities/smoothScroll';

const InlineRating = ({
  reviewsData,
}) => (
  <div className="inline-rating">
    { Object.keys(reviewsData).map((item) => (
      <div className="aggregate-rating" key={item} itemProp="aggregateRating" itemScope="" itemType="">
        <div className="empty-stars">
          <DisplayStar
            starPercentage={reviewsData[item].ReviewStatistics.AverageOverallRating}
          />
          <div className="histogram-data">
            <div className="histogram-title">
              {reviewsData[item].ReviewStatistics.TotalReviewCount}
              {' '}
              {Drupal.t('reviews')}
            </div>
            <RatingSummary
              histogramData={reviewsData[item].ReviewStatistics.RatingDistribution}
              totalReviewCount={reviewsData[item].ReviewStatistics.TotalReviewCount}
            />
          </div>
        </div>
        <span>
          (
          <a onClick={(e) => smoothScrollTo(e, '#reviews-section')} href="#">{reviewsData[item].ReviewStatistics.TotalReviewCount}</a>
          )
        </span>
      </div>
    ))}
  </div>
);
export default InlineRating;
