import React from 'react';
import RatingSummary from './RatingSummary';
import ConditionalView from '../../../common/components/conditional-view';
import { smoothScrollTo } from '../../../utilities/smoothScroll';
import getStringMessage from '../../../../../../js/utilities/strings';
import DisplayStar from '../stars';
import { trackFeaturedAnalytics } from '../../../utilities/analytics';

function clickHandler(e, callbackFn) {
  if (callbackFn === undefined) {
    smoothScrollTo(e, '#reviews-section', '', '', 'auto');
  } else {
    e.preventDefault();
    callbackFn(e);
  }
  // Process review count click as user clicks on count link.
  const analyticsData = {
    type: 'Used',
    name: 'link',
    detail1: 'review_count',
    detail2: 'PrimaryRatingSummary',
  };
  trackFeaturedAnalytics(analyticsData);
}
const InlineRating = ({
  reviewsData,
  childClickHandler,
}) => (
  <div className="inline-rating">
    <div className="aggregate-rating" itemProp="aggregateRating" itemScope="" itemType="">
      <div className="empty-stars">
        <a onClick={(e) => clickHandler(e, childClickHandler)} href="#">
          <DisplayStar
            starPercentage={reviewsData.FilteredReviewStatistics.AverageOverallRating}
          />
        </a>
        <ConditionalView condition={window.innerWidth >= 1024}>
          <div className="histogram-data">
            <div className="histogram-title">
              {reviewsData.FilteredReviewStatistics.TotalReviewCount}
              {' '}
              {reviewsData.FilteredReviewStatistics.TotalReviewCount > 1
                ? getStringMessage('reviews')
                : getStringMessage('review')}
            </div>
            <RatingSummary
              histogramData={reviewsData.FilteredReviewStatistics.RatingDistribution}
              totalReviewCount={reviewsData.TotalReviewCount}
            />
          </div>
        </ConditionalView>
      </div>
      <span>
        (
        <a onClick={(e) => clickHandler(e, childClickHandler)} href="#">{reviewsData.TotalReviewCount}</a>
        )
      </span>
    </div>
  </div>
);

export default InlineRating;
