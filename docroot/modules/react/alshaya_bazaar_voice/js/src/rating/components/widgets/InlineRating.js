import React from 'react';
import RatingSummary from './RatingSummary';
import DisplayStar from '../stars/DisplayStar';
import ConditionalView from '../../../common/components/conditional-view';
import smoothScrollTo from '../../../utilities/smoothScroll';
import getStringMessage from '../../../../../../js/utilities/strings';

const InlineRating = ({
  reviewsData,
}) => (
  <div className="inline-rating">
    <div className="aggregate-rating" itemProp="aggregateRating" itemScope="" itemType="">
      <div className="empty-stars">
        <DisplayStar
          starPercentage={reviewsData.ReviewStatistics.AverageOverallRating}
        />
        <ConditionalView condition={window.innerWidth >= 1024}>
          <div className="histogram-data">
            <div className="histogram-title">
              {reviewsData.ReviewStatistics.TotalReviewCount}
              {' '}
              {getStringMessage('reviews')}
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
