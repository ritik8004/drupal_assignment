import React from 'react';
import RatingSummary from './RatingSummary';
import DisplayStar from '../stars/DisplayStar';
import ConditionalView from '../../../common/components/conditional-view';
import smoothScrollTo from '../../../utilities/smoothScroll';

const InlineRating = ({
  reviewsData,
}) => {
  if (reviewsData !== undefined) {
    return (
      <div className="inline-rating">
        { Object.keys(reviewsData).map((item) => (
          <div className="aggregate-rating" key={item} itemProp="aggregateRating" itemScope="" itemType="">
            <div className="empty-stars">
              <DisplayStar
                starPercentage={reviewsData[item].ReviewStatistics.AverageOverallRating}
              />
              <ConditionalView condition={window.innerWidth >= 1024}>
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
              </ConditionalView>
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
  }
  return (
    <div className="inline-rating">
      <div className="aggregate-rating">
        <a onClick={(e) => smoothScrollTo(e, '#reviews-section')} className="write-review" href="#">{Drupal.t('Write a Review')}</a>
      </div>
    </div>
  );
};
export default InlineRating;
