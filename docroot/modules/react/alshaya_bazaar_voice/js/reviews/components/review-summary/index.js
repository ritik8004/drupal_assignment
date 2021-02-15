import React from 'react';
import DisplayStar from '../../../rating/components/stars/DisplayStar';
import ConditionalView from '../../../common/components/conditional-view';
import ReviewInformation from '../review-info';
import ReviewDescription from '../review-desc';

const ReviewSummary = () => {
  /* TODO: BE to use a helper rather then directly using localstorage. */
  const reviewsSummary = JSON.parse(localStorage.getItem('ReviewsSummary'));
  const reviewsProduct = JSON.parse(localStorage.getItem('ReviewsProduct'));
  if (reviewsSummary !== undefined) {
    return (
      <div className="reviews-wrapper">
        { Object.keys(reviewsSummary).map((item) => (
          <div className="review-summary" key={reviewsSummary[item].Id}>

            <ConditionalView condition={window.innerWidth < 768}>
              <DisplayStar
                StarPercentage={reviewsSummary[item].Rating}
              />
              <div className="review-title">{reviewsSummary[item].Title}</div>
            </ConditionalView>

            <ReviewInformation
              reviewInformationData={reviewsSummary[item]}
              reviewTooltipInfo={
                reviewsProduct[reviewsSummary[item].ProductId].ReviewStatistics
              }
            />

            <ReviewDescription
              reviewDescriptionData={reviewsSummary[item]}
            />
          </div>
        ))}
      </div>
    );
  }
  return (null);
};

export default ReviewSummary;
