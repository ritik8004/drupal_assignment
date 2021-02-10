import React from 'react';
import DisplayStar from '../../../rating/components/stars/DisplayStar';
import ConditionalView from '../../../common/components/conditional-view';
import ReviewInformation from '../review-info';
import ReviewDescription from '../review-desc';

const ReviewSummary = () => {
  /* TODO: BE to use a helper rather then directly using localstorage. */
  const ReviewsSummary = JSON.parse(localStorage.getItem('ReviewsSummary'));
  const ReviewsProduct = JSON.parse(localStorage.getItem('ReviewsProduct'));
  if (ReviewsSummary !== undefined) {
    return (
      <div className="reviews-wrapper">
        { Object.keys(ReviewsSummary).map((item) => (
          <div className="review-summary" key={ReviewsSummary[item].Id}>

            <ConditionalView condition={window.innerWidth < 768}>
              <DisplayStar
                StarPercentage={ReviewsSummary[item].Rating}
              />
              <div className="review-title">{ReviewsSummary[item].Title}</div>
            </ConditionalView>

            <ReviewInformation
              ReviewInformationData={ReviewsSummary[item]}
              ReviewTooltipInfo={
                ReviewsProduct[ReviewsSummary[item].ProductId].ReviewStatistics
              }
            />

            <ReviewDescription
              ReviewDescriptionData={ReviewsSummary[item]}
            />
          </div>
        ))}
      </div>
    );
  }
  return (null);
};

export default ReviewSummary;
