import React from 'react';
import RatingSummary from '../../../rating/components/widgets/RatingSummary';
import DisplayStar from '../../../rating/components/stars/DisplayStar';
import CombineDisplay from '../review-combine-display';
import ConditionalView from '../../../common/components/conditional-view';
import WriteReviewButton from '../reviews-full-submit';


const ReviewHistogram = ({
  overallSummary,
}) => {
  if (overallSummary === undefined) {
    return null;
  }
  return (
    <>
      <div className="overall-summary-title">{Drupal.t('Ratings + Reviews')}</div>
      <div className="overall-summary">
        { Object.keys(overallSummary).map((item) => (
          <React.Fragment key={item}>
            <div className="histogram-wrapper" key={item}>
              <ConditionalView condition={window.innerWidth < 768}>
                <WriteReviewButton />
              </ConditionalView>
              <DisplayStar
                starPercentage={overallSummary[item].ReviewStatistics.AverageOverallRating}
              />
              <div className="average-rating">
                {(
                  parseFloat(overallSummary[item].ReviewStatistics.AverageOverallRating).toFixed(1)
                )}
              </div>
              <div className="histogram-data">
                <div className="histogram-title">
                  {((
                    overallSummary[item].ReviewStatistics.RecommendedCount
                    / overallSummary[item].ReviewStatistics.TotalReviewCount).toFixed(1) * 100
                  )}
                  {'% '}
                  {Drupal.t('of Customers Recommended the Product')}
                </div>
                <RatingSummary
                  histogramData={overallSummary[item].ReviewStatistics.RatingDistribution}
                  totalReviewCount={overallSummary[item].ReviewStatistics.TotalReviewCount}
                />
                <ConditionalView condition={window.innerWidth < 768}>
                  <div className="secondary-summary">
                    <CombineDisplay
                      starSliderCombine={
                        overallSummary[item].ReviewStatistics.SecondaryRatingsAverages
                      }
                    />
                  </div>
                </ConditionalView>
              </div>
            </div>
            <div className="secondary-summary">
              <ConditionalView condition={window.innerWidth > 767}>
                <WriteReviewButton />
                <CombineDisplay
                  starSliderCombine={
                    overallSummary[item].ReviewStatistics.SecondaryRatingsAverages
                  }
                />
              </ConditionalView>
            </div>
          </React.Fragment>
        ))}
      </div>
    </>
  );
};
export default ReviewHistogram;
