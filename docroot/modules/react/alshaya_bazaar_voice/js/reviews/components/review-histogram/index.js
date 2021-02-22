import React from 'react';
import RatingSummary from '../../../rating/components/widgets/RatingSummary';
import DisplayStar from '../../../rating/components/stars/DisplayStar';
import ReviewButton from '../review-button';
import IndividualReviewSlider from '../individual-review-slider';
import IndividualReviewStar from '../individual-review-star';
import ConditionalView from '../../../common/components/conditional-view';


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
                <ReviewButton buttonText={Drupal.t('write a review')} />
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
                  HistogramData={overallSummary[item].ReviewStatistics.RatingDistribution}
                  TotalReviewCount={overallSummary[item].ReviewStatistics.TotalReviewCount}
                />
                <ConditionalView condition={window.innerWidth < 768}>
                  <div className="secondary-summary">
                    <div className="overall-product-rating">
                      <IndividualReviewSlider
                        sliderData={overallSummary[item].ReviewStatistics.SecondaryRatingsAverages}
                      />

                      <IndividualReviewStar
                        customerValue={
                          overallSummary[item].ReviewStatistics.SecondaryRatingsAverages
                        }
                      />
                    </div>
                  </div>
                </ConditionalView>
              </div>
            </div>
            <div className="secondary-summary">
              <ConditionalView condition={window.innerWidth > 767}>
                <ReviewButton buttonText={Drupal.t('write a review')} />
                <div className="overall-product-rating">
                  <IndividualReviewSlider
                    sliderData={overallSummary[item].ReviewStatistics.SecondaryRatingsAverages}
                  />

                  <IndividualReviewStar
                    customerValue={overallSummary[item].ReviewStatistics.SecondaryRatingsAverages}
                  />
                </div>
              </ConditionalView>
            </div>
          </React.Fragment>
        ))}
      </div>
    </>
  );
};
export default ReviewHistogram;
