import React from 'react';
import RatingSummary from './RatingSummary';
import DisplayStar from '../stars/DisplayStar';
import ConditionalView from '../../../common/components/conditional-view';
import smoothScrollTo from '../../../utilities/smoothScroll';

const InlineRating = ({
  reviewsData,
}) => (
  <div className="inline-rating">
    <div className="aggregate-rating" itemProp="aggregateRating" itemScope="" itemType="">
      <div className="empty-stars">
        <a onClick={(e) => smoothScrollTo(e, '#reviews-section')} href="#">
          <DisplayStar
            starPercentage={reviewsData.ReviewStatistics.AverageOverallRating}
          />
        </a>
        <ConditionalView condition={window.innerWidth >= 1024}>
          <div className="histogram-data">
            <div className="histogram-title">
              {reviewsData.ReviewStatistics.TotalReviewCount}
              {' '}
              {Drupal.t('reviews')}
            </div>
            <RatingSummary
              histogramData={reviewsData.ReviewStatistics.RatingDistribution}
              totalReviewCount={reviewsData.TotalReviewCount}
            />
          </div>
        </ConditionalView>
      </div>
      <span>
        (
        <a onClick={(e) => smoothScrollTo(e, '#reviews-section')} href="#">{reviewsData.TotalReviewCount}</a>
        )
      </span>
    </div>
  </div>
);

export default InlineRating;
