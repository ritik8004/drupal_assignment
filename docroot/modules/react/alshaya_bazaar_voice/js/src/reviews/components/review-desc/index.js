import React from 'react';
import ReviewFeedback from '../review-feedback';
import ConditionalView from '../../../common/components/conditional-view';
import ReviewCommentForm from '../review-comment-form';
import ReviewCommentDisplay from '../review-comment-display';
import ReviewAdditionalAttributes from '../review-additional-attributes';
import ReviewPhotos from '../review-photo';
import getStringMessage from '../../../../../../js/utilities/strings';
import { getDate } from '../../../../../../js/utilities/dateUtility';
import DisplayStar from '../../../rating/components/stars';
import ReviewResponseDisplay from '../review-response-display';
import { getbazaarVoiceSettings, getLanguageCode } from '../../../utilities/api/request';
import TranslateByGoogle from '../../../common/components/translate-by-google';
import { hasValue } from '../../../../../../js/utilities/conditionsUtility';

const ReviewDescription = ({
  reviewDescriptionData,
  reviewsComment,
  isNewPdpLayout,
}) => {
  let newPdp = isNewPdpLayout;
  newPdp = (newPdp === undefined) ? false : newPdp;
  const bazaarVoiceSettings = getbazaarVoiceSettings();
  const reviewId = reviewDescriptionData.Id;
  const contentLocale = reviewDescriptionData.ContentLocale.substring(0, 2);
  const enableTranslation = bazaarVoiceSettings.reviews.bazaar_voice.enable_google_translation;
  const charsLimit = bazaarVoiceSettings.reviews.bazaar_voice.translate_chars_limit;

  if (reviewDescriptionData !== undefined) {
    const date = getDate(reviewDescriptionData.SubmissionTime, getLanguageCode());
    return (
      <div className="review-detail-right">
        <div className="review-details">
          <ConditionalView condition={(window.innerWidth > 767) && (!newPdp)}>
            <DisplayStar
              starPercentage={reviewDescriptionData.Rating}
            />
            <div id={`${reviewId}-review-title`} className="review-title">{reviewDescriptionData.Title}</div>
            <div id={`${reviewId}-review-date`} className="review-date">{`${date}`}</div>
          </ConditionalView>
          { reviewDescriptionData.ReviewText && (
            <div id={`${reviewId}-review-text`} className="review-text">
              {reviewDescriptionData.ReviewText.split('\n').map((item, idx) => (
                <span key={idx.toString()}>
                  {item}
                  <br />
                </span>
              ))}
            </div>
          )}
          { reviewDescriptionData.ReviewText && (
            <ConditionalView
              condition={enableTranslation
              && reviewDescriptionData.ReviewText.length < charsLimit
              && contentLocale !== getLanguageCode()}
            >
              <TranslateByGoogle id={reviewId} contentLocale={contentLocale} contentType="review" />
            </ConditionalView>
          )}
          <ReviewAdditionalAttributes
            additionalFieldsData={reviewDescriptionData.AdditionalFields}
            additionalFieldsOrder={reviewDescriptionData.AdditionalFieldsOrder}
            tagDimensionsData={reviewDescriptionData.TagDimensions}
            tagDimensionsOrder={reviewDescriptionData.TagDimensionsOrder}
          />
          <ConditionalView condition={hasValue(reviewDescriptionData.Photos)}>
            <ReviewPhotos photoCollection={reviewDescriptionData.Photos} />
          </ConditionalView>
          <ConditionalView condition={reviewDescriptionData.IsRecommended
            || !reviewDescriptionData.IsSyndicated}
          >
            <div className="review-inline-feedback">
              <div>
                <ConditionalView condition={reviewDescriptionData.IsRecommended !== false
                  && reviewDescriptionData.IsRecommended !== null}
                >
                  <div className="review-recommendation">
                    <span className="review-recommendation-icon" />
                    <span>{`${reviewDescriptionData.IsRecommended ? getStringMessage('yes') : getStringMessage('no')},`}</span>
                    <span className="review-recommendation-text">{getStringMessage('review_recommendation_text')}</span>
                  </div>
                </ConditionalView>
                <ConditionalView condition={!reviewDescriptionData.IsSyndicated}>
                  <div className="review-feedback">
                    <ReviewFeedback
                      negativeCount={reviewDescriptionData.TotalNegativeFeedbackCount}
                      positiveCount={reviewDescriptionData.TotalPositiveFeedbackCount}
                      contentId={reviewDescriptionData.Id}
                      contentType="review"
                    />
                  </div>
                </ConditionalView>
              </div>
              <ConditionalView condition={!reviewDescriptionData.IsSyndicated
                && bazaarVoiceSettings.reviews.bazaar_voice.comment_submission}
              >
                <ReviewCommentForm
                  ReviewId={reviewDescriptionData.Id}
                />
              </ConditionalView>
              <ConditionalView condition={reviewDescriptionData.TotalClientResponseCount > 0}>
                <ReviewResponseDisplay
                  reviewId={reviewDescriptionData.Id}
                  reviewResponses={reviewDescriptionData.ClientResponses}
                />
              </ConditionalView>
              <ConditionalView
                condition={bazaarVoiceSettings.reviews.bazaar_voice.comment_submission}
              >
                <div className="review-comment-display">
                  <ReviewCommentDisplay
                    reviewId={reviewDescriptionData.Id}
                    reviewsComment={reviewsComment}
                  />
                </div>
              </ConditionalView>
            </div>
          </ConditionalView>
          {reviewDescriptionData.IsSyndicated
            && (
            <div className="review-syndicated">
              <div className="review-syndicated-image">
                <span><img src={reviewDescriptionData.SyndicationSource.LogoImageUrl} /></span>
              </div>
              <div className="review-syndicated-source">
                <span>{`${getStringMessage('review_syndicated_text')} `}</span>
                <span>{reviewDescriptionData.SyndicationSource.Name}</span>
              </div>
            </div>
            )}
        </div>
      </div>
    );
  }
  return (null);
};

export default ReviewDescription;
