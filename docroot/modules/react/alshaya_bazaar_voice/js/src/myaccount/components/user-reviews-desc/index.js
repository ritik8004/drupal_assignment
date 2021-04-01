import React from 'react';
import { getDate } from '../../../../../../js/utilities/dateUtility';
import ConditionalView from '../../../common/components/conditional-view';
import ReviewPhoto from '../../../reviews/components/review-photo';

const UserReviewsDescription = ({
  reviewsIndividualSummary,
}) => {
  if (reviewsIndividualSummary === null) {
    return null;
  }
  const reviewDate = getDate(reviewsIndividualSummary.SubmissionTime);
  return (
    <div className="reviews-block">
      <div className="review-title">{reviewsIndividualSummary.Title}</div>
      <div className="review-date">{reviewDate}</div>
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
