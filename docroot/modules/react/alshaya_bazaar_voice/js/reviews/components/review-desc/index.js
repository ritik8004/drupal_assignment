import React from 'react';
import DisplayStar from '../../../rating/components/stars/DisplayStar';
import ReviewFeedback from '../review-feedback';
import ConditionalView from '../../../common/components/conditional-view';

const ReviewDescription = ({
  reviewDescriptionData,
}) => {
  if (reviewDescriptionData !== undefined) {
    const date = new Date(reviewDescriptionData.SubmissionTime);
    return (
      <div className="review-detail-right">
        <div className="review-details">

          <ConditionalView condition={window.innerWidth > 767}>
            <DisplayStar
              starPercentage={reviewDescriptionData.Rating}
            />
            <div className="review-title">{reviewDescriptionData.Title}</div>
            <div className="review-date">{`${date.toLocaleString('default', { month: 'short' })} ${date.getDate()}, ${date.getFullYear()}`}</div>
          </ConditionalView>

          <div className="review-text">{reviewDescriptionData.ReviewText}</div>
          <div className="review-photo">{reviewDescriptionData.Photo}</div>
          <div className="review-feedback">
            <ReviewFeedback
              NegativeFeedbackCount={reviewDescriptionData.TotalNegativeFeedbackCount}
              PositiveFeedbackCount={reviewDescriptionData.TotalPositiveFeedbackCount}
              IsSyndicatedReview={reviewDescriptionData.IsSyndicated}
              ReviewId={reviewDescriptionData.Id}
            />
            <div className="review-feedback-comment">
              <button type="button">{Drupal.t('comment')}</button>
            </div>
          </div>
        </div>
      </div>
    );
  }
  return (null);
};

export default ReviewDescription;
