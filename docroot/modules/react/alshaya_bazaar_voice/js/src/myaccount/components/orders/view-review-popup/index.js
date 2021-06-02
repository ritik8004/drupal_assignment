import React from 'react';
import ConditionalView from '../../../../common/components/conditional-view';
import UserReviewsDescription from '../../reviews/user-reviews-desc';
import IndividualReviewSlider from '../../../../reviews/components/individual-review-slider';
import DisplayStar from '../../../../rating/components/stars';

const ViewReviewPopup = ({
  reviewData, productData,
}) => (
  <div className="user-reviews">
    <div className="user-desc">
      <DisplayStar
        starPercentage={reviewData.Rating}
      />
      <UserReviewsDescription
        reviewsIndividualSummary={reviewData}
      />
    </div>
    <ConditionalView condition={window.innerWidth > 767 && productData !== null}>
      <div className="user-secondary-rating">
        <IndividualReviewSlider
          sliderData={productData.ReviewStatistics.SecondaryRatingsAverages}
          secondaryRatingsOrder={productData.ReviewStatistics.SecondaryRatingsAveragesOrder}
        />
      </div>
    </ConditionalView>
  </div>
);

export default ViewReviewPopup;
