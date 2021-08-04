import React from 'react';
import { getDate } from '../../../../../../../js/utilities/dateUtility';
import ConditionalView from '../../../../common/components/conditional-view';
import ReviewPhotos from '../../../../reviews/components/review-photo';
import { getLanguageCode } from '../../../../utilities/api/request';

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
      <div className="review-text">{reviewsIndividualSummary.ReviewText}</div>
      <ConditionalView condition={reviewsIndividualSummary.Photos
        && reviewsIndividualSummary.Photos.length > 0}
      >
        <ReviewPhotos photoCollection={reviewsIndividualSummary.Photos} />
      </ConditionalView>
    </div>
  );
};

export default UserReviewsDescription;
