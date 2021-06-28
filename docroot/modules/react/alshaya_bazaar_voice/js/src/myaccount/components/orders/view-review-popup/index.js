import React from 'react';
import UserReviewsDescription from '../../reviews/user-reviews-desc';
import IndividualReviewSlider from '../../../../reviews/components/individual-review-slider';
import DisplayStar from '../../../../rating/components/stars';

const ViewReviewPopup = ({
  reviewData,
}) => {
  if (reviewData === null) {
    return null;
  }
  return (
    <div className="user-reviews">
      <div className="user-desc">
        <DisplayStar
          starPercentage={reviewData.Rating}
        />
        <UserReviewsDescription
          reviewsIndividualSummary={reviewData}
        />
      </div>
      <div className="user-secondary-rating">
        <IndividualReviewSlider
          sliderData={reviewData.SecondaryRatings}
          secondaryRatingsOrder={reviewData.SecondaryRatingsOrder}
        />
      </div>
    </div>
  );
};

export default ViewReviewPopup;
