import React from 'react';
import DisplayStar from '../../../rating/components/stars/DisplayStar';
import ReviewFeedback from '../review-feedback';
import ConditionalView from '../../../common/components/conditional-view';
import ReviewCommentForm from '../review-comment-form';
import ReviewCommentDisplay from '../review-comment-display';
import ReviewAdditionalAttributes from '../review-additional-attributes';

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

          <ReviewAdditionalAttributes
            reviewAdditionalAttributesData={reviewDescriptionData.TagDimensions}
          />

          <ReviewAdditionalAttributes
            reviewAdditionalAttributesData={reviewDescriptionData.AdditionalFields}
            includes="_textarea"
          />

          <div className="review-photo">{reviewDescriptionData.Photo}</div>
          <div className="review-inline-feedback">
            <div>
              <ConditionalView condition={reviewDescriptionData.IsRecommended !== false
                && reviewDescriptionData.IsRecommended !== null}
              >
                <div className="review-recommendation">
                  <span className="review-recommendation-icon">{Drupal.t('recommendation-icon')}</span>
                  <span>{`${reviewDescriptionData.IsRecommended ? Drupal.t('yes') : Drupal.t('no')},`}</span>
                  <span className="review-recommendation-text">{Drupal.t('I would recommend this product.')}</span>
                </div>
              </ConditionalView>
              <div className="review-feedback">
                <ReviewFeedback
                  negativeCount={reviewDescriptionData.TotalNegativeFeedbackCount}
                  positiveCount={reviewDescriptionData.TotalPositiveFeedbackCount}
                  isSyndicatedReview={reviewDescriptionData.IsSyndicated}
                  contentId={reviewDescriptionData.Id}
                  contentType="review"
                />
              </div>
            </div>
            <ReviewCommentForm
              ReviewId={reviewDescriptionData.Id}
            />
            <div className="review-comment-display">
              <ReviewCommentDisplay
                ReviewId={reviewDescriptionData.Id}
              />
            </div>
          </div>
        </div>
      </div>
    );
  }
  return (null);
};

export default ReviewDescription;
