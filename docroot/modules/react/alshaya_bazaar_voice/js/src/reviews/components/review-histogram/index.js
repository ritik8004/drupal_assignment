import React from 'react';
import RatingSummary from '../../../rating/components/widgets/RatingSummary';
import CombineDisplay from '../review-combine-display';
import ConditionalView from '../../../common/components/conditional-view';
import WriteReviewButton from '../reviews-full-submit';
import getStringMessage from '../../../../../../js/utilities/strings';
import DisplayStar from '../../../rating/components/stars';

const ReviewHistogram = ({
  overallSummary, isNewPdpLayout,
}) => {
  if (overallSummary === undefined) {
    return null;
  }

  if (isNewPdpLayout === undefined) {
    isNewPdpLayout = false;
  }

  return (
    <>
      <div className="overall-summary-title">{getStringMessage('ratings_reviews')}</div>
      <div className="overall-summary">
        { Object.keys(overallSummary).map((item) => (
          <React.Fragment key={item}>
            <div className="histogram-wrapper" key={item}>
              <ConditionalView condition={(window.innerWidth < 768) || isNewPdpLayout}>
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
                  {
                    Drupal.t('@customerCount% of Customers Recommended the Product', {
                      '@customerCount': ((
                        overallSummary[item].ReviewStatistics.RecommendedCount
                        / overallSummary[item].ReviewStatistics.TotalReviewCount).toFixed(1) * 100
                      ),
                    })
                  }
                </div>
                <RatingSummary
                  histogramData={overallSummary[item].ReviewStatistics.RatingDistribution}
                  totalReviewCount={overallSummary[item].ReviewStatistics.TotalReviewCount}
                />
                <ConditionalView condition={(window.innerWidth < 768) || isNewPdpLayout}>
                  <div className="secondary-summary">
                    <CombineDisplay
                      starSliderCombine={
                        overallSummary[item].ReviewStatistics.SecondaryRatingsAverages
                      }
                      secondaryRatingsOrder={
                        overallSummary[item].ReviewStatistics.SecondaryRatingsAveragesOrder
                      }
                    />
                  </div>
                </ConditionalView>
              </div>
            </div>
            <div className="secondary-summary">
              <ConditionalView condition={(window.innerWidth > 767) && (!isNewPdpLayout)}>
                <WriteReviewButton />
                <CombineDisplay
                  starSliderCombine={
                    overallSummary[item].ReviewStatistics.SecondaryRatingsAverages
                  }
                  secondaryRatingsOrder={
                    overallSummary[item].ReviewStatistics.SecondaryRatingsAveragesOrder
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
