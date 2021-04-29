import React from 'react';
import { getDate } from '../../../../../../js/utilities/dateUtility';
import ConditionalView from '../../../common/components/conditional-view';
import ReviewPhoto from '../../../reviews/components/review-photo';
import IndividualReviewSlider from '../../../reviews/components/individual-review-slider';
import { getLanguageCode } from '../../../utilities/api/request';

const UserReviewsDescription = ({
  reviewsIndividualSummary,
}) => {
  if (reviewsIndividualSummary === null) {
    return null;
  }
  const reviewDate = getDate(reviewsIndividualSummary.SubmissionTime, getLanguageCode());
  return (
    <div className="reviews-block">
      <div className="review-title">{reviewsIndividualSummary.Title}</div>
      <div className="review-date">{reviewDate}</div>
      <ConditionalView condition={window.innerWidth < 768}>
        <div className="user-secondary-rating">
          <IndividualReviewSlider
            sliderData={reviewsIndividualSummary.SecondaryRatings}
          />
        </div>
      </ConditionalView>
      <div className="review-text">{reviewsIndividualSummary.ReviewText}</div>
      <div className="review-photos">
        <ConditionalView condition={reviewsIndividualSummary.Photos
          && reviewsIndividualSummary.Photos.length > 0}
        >
          <ReviewPhoto photoCollection={reviewsIndividualSummary.Photos} />
        </ConditionalView>
      </div>
    </div>
  );
};

export default UserReviewsDescription;
