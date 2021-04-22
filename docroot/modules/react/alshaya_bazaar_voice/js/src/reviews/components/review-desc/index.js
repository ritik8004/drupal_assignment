import React from 'react';
import ReviewFeedback from '../review-feedback';
import ConditionalView from '../../../common/components/conditional-view';
import ReviewCommentForm from '../review-comment-form';
import ReviewCommentDisplay from '../review-comment-display';
import ReviewAdditionalAttributes from '../review-additional-attributes';
import ReviewPhoto from '../review-photo';
import getStringMessage from '../../../../../../js/utilities/strings';
import { getDate } from '../../../../../../js/utilities/dateUtility';
import DisplayStar from '../../../rating/components/stars';

const ReviewDescription = ({
  reviewDescriptionData,
  reviewsComment,
}) => {
  if (reviewDescriptionData !== undefined) {
    const date = getDate(reviewDescriptionData.SubmissionTime);
    return (
      <div className="review-detail-right">
        <div className="review-details">

          <ConditionalView condition={window.innerWidth > 767}>
            <DisplayStar
              starPercentage={reviewDescriptionData.Rating}
            />
            <div className="review-title">{reviewDescriptionData.Title}</div>
            <div className="review-date">{`${date}`}</div>
          </ConditionalView>
          <div className="review-text">{reviewDescriptionData.ReviewText}</div>
          <ReviewAdditionalAttributes
            additionalFieldsData={reviewDescriptionData.AdditionalFields}
            additionalFieldsOrder={reviewDescriptionData.AdditionalFieldsOrder}
            tagDimensionsData={reviewDescriptionData.TagDimensions}
            tagDimensionsOrder={reviewDescriptionData.TagDimensionsOrder}
          />
          <ConditionalView condition={reviewDescriptionData.Photos
            && reviewDescriptionData.Photos.length > 0}
          >
            <ReviewPhoto photoCollection={reviewDescriptionData.Photos} />
          </ConditionalView>
          <div className="review-inline-feedback">
            <div>
              <ConditionalView condition={reviewDescriptionData.IsRecommended !== false
                && reviewDescriptionData.IsRecommended !== null}
              >
                <div className="review-recommendation">
                  <span className="review-recommendation-icon" />
                  <span>{`${reviewDescriptionData.IsRecommended ? Drupal.t('yes') : Drupal.t('no')},`}</span>
                  <span className="review-recommendation-text">{getStringMessage('review_recommendation_text')}</span>
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
                reviewId={reviewDescriptionData.Id}
                reviewsComment={reviewsComment}
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
