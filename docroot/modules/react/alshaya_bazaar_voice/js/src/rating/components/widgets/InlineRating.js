import React from 'react';
import RatingSummary from './RatingSummary';
import DisplayStar from '../stars/DisplayStar';
import smoothScrollTo from '../../../utilities/smoothScroll';

const InlineRating = ({
  ReviewsData,
}) => (
  <div className="inline-rating">
    { Object.keys(ReviewsData).map((item) => (
      <div className="aggregate-rating" key={item} itemProp="aggregateRating" itemScope="" itemType="">
        <div className="empty-stars">
          <DisplayStar
            starPercentage={ReviewsData[item].ReviewStatistics.AverageOverallRating}
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
          <a onClick={(e) => smoothScrollTo(e, '#reviews-section')} href="#">{ReviewsData[item].ReviewStatistics.TotalReviewCount}</a>
          )
        </span>
      </div>
    ))}
  </div>
);
export default InlineRating;
