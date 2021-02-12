import React from 'react';
import DisplayStar from '../../../rating/components/stars/DisplayStar';
import ReviewFeedback from '../review-feedback';
import ConditionalView from '../../../common/components/conditional-view';

const ReviewDescription = ({
  ReviewDescriptionData,
}) => {
  if (ReviewDescriptionData !== undefined) {
    const date = new Date(ReviewDescriptionData.SubmissionTime);
    return (
      <div className="review-detail-right">
        <div className="review-details">

          <ConditionalView condition={window.innerWidth > 767}>
            <DisplayStar
              StarPercentage={ReviewDescriptionData.Rating}
            />
            <div className="review-title">{ReviewDescriptionData.Title}</div>
            <div className="review-date">{`${date.toLocaleString('default', { month: 'short' })} ${date.getDate()}, ${date.getFullYear()}`}</div>
          </ConditionalView>

          <div className="review-text">{ReviewDescriptionData.ReviewText}</div>
          <div className="review-photo">{ReviewDescriptionData.Photo}</div>
          <div className="review-feedback">
            <ReviewFeedback 
              NegativeFeedbackCount = {ReviewDescriptionData.TotalNegativeFeedbackCount}
              PositiveFeedbackCount = {ReviewDescriptionData.TotalPositiveFeedbackCount}
              IsSyndicatedReview = {ReviewDescriptionData.IsSyndicated}
              ReviewId = {ReviewDescriptionData.Id}
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
