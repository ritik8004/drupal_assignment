import React from 'react';
import RatingSummary from './RatingSummary';
import ConditionalView from '../../../common/components/conditional-view';
import smoothScrollTo from '../../../utilities/smoothScroll';
import getStringMessage from '../../../../../../js/utilities/strings';
import DisplayStar from '../stars';

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
              {reviewsData.ReviewStatistics.TotalReviewCount > 1
                ? getStringMessage('reviews')
                : getStringMessage('review')}
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
