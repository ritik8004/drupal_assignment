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

  let newPdp = isNewPdpLayout;
  newPdp = (newPdp === undefined) ? false : newPdp;

  return (
    <>
      <div className="overall-summary-title">{getStringMessage('ratings_reviews')}</div>
      <div className="overall-summary">
        { Object.keys(overallSummary).map((item) => (
          <React.Fragment key={item}>
            <div className="histogram-wrapper" key={item}>
              <ConditionalView condition={(window.innerWidth < 768) || newPdp}>
                <WriteReviewButton />
              </ConditionalView>
              <DisplayStar
                starPercentage={overallSummary[item].FilteredReviewStatistics.AverageOverallRating}
              />
              <div className="average-rating">
                {(
                  parseFloat(overallSummary[item].FilteredReviewStatistics.AverageOverallRating)
                    .toFixed(1)
                )}
              </div>
              <div className="histogram-data">
                <div className="histogram-title">
                  {
                    Drupal.t('@customerCount% of Customers Recommended the Product', {
                      '@customerCount': ((
                        overallSummary[item].FilteredReviewStatistics.RecommendedCount
                        / overallSummary[item].FilteredReviewStatistics.TotalReviewCount)
                        .toFixed(1) * 100
                      ),
                    })
                  }
                </div>
                <RatingSummary
                  histogramData={overallSummary[item].FilteredReviewStatistics.RatingDistribution}
                  totalReviewCount={overallSummary[item].FilteredReviewStatistics.TotalReviewCount}
                />
                <ConditionalView condition={(window.innerWidth < 768) || newPdp}>
                  <div className="secondary-summary">
                    <CombineDisplay
                      starSliderCombine={
                        overallSummary[item].FilteredReviewStatistics.SecondaryRatingsAverages
                      }
                      secondaryRatingsOrder={
                        overallSummary[item].FilteredReviewStatistics.SecondaryRatingsAveragesOrder
                      }
                    />
                  </div>
                </ConditionalView>
              </div>
            </div>
            <div className="secondary-summary">
              <ConditionalView condition={(window.innerWidth > 767) && (!newPdp)}>
                <WriteReviewButton />
                <CombineDisplay
                  starSliderCombine={
                    overallSummary[item].FilteredReviewStatistics.SecondaryRatingsAverages
                  }
                  secondaryRatingsOrder={
                    overallSummary[item].FilteredReviewStatistics.SecondaryRatingsAveragesOrder
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
